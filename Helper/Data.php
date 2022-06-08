<?php
/**
 * Copyright 2020 Adorncommerce LLP. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Adorncommerce\ProductReviewRating\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Adorncommerce\ProductReviewRating\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected $_reviewsColFactory;

    protected $_storeManager;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_customerSession = $customerSession;
        $this->_reviewsColFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue('rating/general/enable');
    }

    public function isPopupEnabled()
    {
        return $this->scopeConfig->getValue('rating/general/popup');
    }

    /**
     * @return mixed
     */
    public function getColorCode()
    {
        return$this->scopeConfig->getValue('rating/general/star_color_option');
    }

    public function getGraphicCircle()
    {
        return$this->scopeConfig->getValue('rating/general/graphic_circle');
    }

    public function getRecipientEmail()
    {
        return $this->scopeConfig->getValue('rating/general/review_email_send_identity');
    }

    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    public function getAdminReplyEmailTemplate()
    {
        return $this->scopeConfig->getValue('rating/general/admin_reply_email_template');
    }

    public function getAdminReceiveEmailTemplate()
    {
        return $this->scopeConfig->getValue('rating/general/admin_review_email_template');
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        if ($this->isModuleEnabled()) {
            $template =  'Adorncommerce_ProductReviewRating::product/view/list.phtml';
        } else {
            $template = 'Magento_Review::product/view/list.phtml';
        }
        return $template;
    }
    public function getFormTemplate()
    {
        if ($this->isModuleEnabled()) {
            $template =  'Adorncommerce_ProductReviewRating::form.phtml';
        } else {
            $template = 'Magento_Review::form.phtml';
        }
        return $template;
    }
    public function getCustomerReviewTemplate()
    {
        if ($this->isModuleEnabled()) {
            $template =  'Adorncommerce_ProductReviewRating::customer/view.phtml';
        } else {
            $template = 'Magento_Review::customer/view.phtml';
        }
        return $template;
    }
    public function getReviewTemplate()
    {
        if ($this->isModuleEnabled()) {
            $template =  'Adorncommerce_ProductReviewRating::review.phtml';
        } else {
            $template = 'Magento_Review::review.phtml';
        }
        return $template;
    }

    /**
     * @param $productId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImages($productId){
        $reviewImage = $this->_reviewsCollection = $this->_reviewsColFactory->create()->addStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addEntityFilter(
            'product',
            $productId
        )->addFieldToSelect('review_id');
        $reviewImage->getSelect()->columns('details.image as image')->group('review_id');
        $reviewImage = array_column($reviewImage->getData(), 'image','rating_id');
        $images = [];
        foreach (array_filter($reviewImage) as $img){
                $images[] = json_decode($img);
        }
        if($images){
            return call_user_func_array('array_merge', $images);
        }else{
            return false;
        }
        
    }
}
