<?php
/**
 * Copyright 2020 Adorncommerce LLP. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Adorncommerce\ProductReviewRating\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Review\Model\Review\StatusFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Filesystem;

/**
 * Class ReviewSave
 * @package Adorncommerce\ProductReviewRating\Model\Observer
 */
class ReviewSave implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface 0
     */
    protected $scopeConfig;
    /**
     * @var \Adorncommerce\ProductReviewRating\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var StatusFactory
     */
    protected $_statusFactory;

    protected $request;

    /**
     * ReviewSave constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Adorncommerce\ProductReviewRating\Helper\Data $helperData
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param ManagerInterface $messageManager
     * @param StatusFactory $statusFactory
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Adorncommerce\ProductReviewRating\Helper\Data $helperData,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        ManagerInterface $messageManager,
        StatusFactory $statusFactory,
        UploaderFactory $uploaderFactory,
        AdapterFactory $adapterFactory,
        Filesystem $filesystem,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->_resource = $resource;
        $this->transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_helperData = $helperData;
        $this->_customerFactory = $customerFactory;
        $this->_messageManager = $messageManager;
        $this->_statusFactory = $statusFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helperData->isModuleEnabled()) {
            return $observer;
        }
        try {
            $currentAreacode = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\State')->getAreaCode();
            $review = $observer->getEvent()->getDataObject();
            if ($currentAreacode == 'adminhtml') {
                $connection = $this->_resource;
                $tableName = $connection->getTableName('review_detail');
                $detail = [
                    'admin_reply' => $review->getAdminReply()
                ];
                $select = $connection->getConnection()->select()->from($tableName)->where('review_id = :review_id');
                $detailId = $connection->getConnection()->fetchOne($select, [':review_id' => $review->getId()]);
                if ($detailId) {
                    $condition = ["detail_id = ?" => $detailId];
                    $connection->getConnection()->update($tableName, $detail, $condition);
                } else {
                    $detail['store_id'] = $review->getStoreId();
                    $detail['customer_id'] = $review->getCustomerId();
                    $detail['review_id'] = $review->getId();
                    $connection->getConnection()->insert($tableName, $detail);
                }
                $statusFact = $this->_statusFactory->create()->load($review->getStatusId());
                if ($review['notify_customer'] == true && $review->getCustomerId()) {
                    $customer = $this->_customerFactory->create()->load($review->getCustomerId());
                    $store = $this->_storeManager->getStore();
                    $templateVars['review_status'] = $statusFact->getStatusCode();
                    $templateVars['customer_name'] = $review['nickname'];
                    $templateVars['admin_reply'] = $review['admin_reply'] ? $review['admin_reply'] : "N/A";
                    $templateVars['title'] = $review['title'];
                    $templateVars['customer_review'] = $review['detail'];
                    $templateId = $this->_helperData->getAdminReplyEmailTemplate();
                    $from = $this->_helperData->getRecipientEmail();
                    $template = $this->transportBuilder->setTemplateIdentifier($templateId)
                        ->setTemplateOptions(['area' => 'frontend', 'store' => $store->getId()])
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($customer->getEmail(), $customer->getName())
                        ->getTransport();
                    $template->sendMessage();
                }
            } else {
                $store = $this->_storeManager->getStore();
                $templateVars['customer_name'] = $review['nickname'];
                $templateVars['title'] = $review['title'];
                $templateVars['customer_review'] = $review['detail'];
                $templateVars['review_id'] = $review['review_id'];
                $templateVars['product_id'] = $review['entity_pk_value'];
                $templateId = $this->_helperData->getAdminReceiveEmailTemplate();
                $from = $this->_helperData->getRecipientEmail();
                $email = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $name = $this->scopeConfig->getValue('trans_email/ident_' . $from . '/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $template = $this->transportBuilder->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(['area' => 'frontend', 'store' => $store->getId()])
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($email, $name)
                    ->getTransport();
                $template->sendMessage();
                return $observer;
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        return $observer;
    }
}
