<?php
/**
 * ProductReviewImageSave
 *
 * @copyright Copyright © 2021 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Adorncommerce\ProductReviewRating\Observer;


use Magento\Framework\Message\ManagerInterface;

class ProductReviewImageSave implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
     */
    protected $_mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    protected $_resource;

    protected $_messageManager;


    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        ManagerInterface $messageManager
    )
    {
        $this->_resource = $resource;
        $this->_request = $request;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_messageManager = $messageManager;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $review = $observer->getEvent()->getDataObject();
        $media = $this->_request->getFiles('image');
        $target = $this->_mediaDirectory->getAbsolutePath('product_review_image');

        if ($media) {
            try {
                $files = [];
                for ($i = 0; $i < count($media); $i++) {
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => 'image[' . $i . ']']);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(true);
                    $uploader->setAllowCreateFolders(true);
                    $result = $uploader->save($target);
                    $files[] =$result['file'];
                }
                $connection = $this->_resource;
                $tableName = $connection->getTableName('review_detail');
                $detail = [
                    'image' => json_encode($files)
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
            } catch (\Exception $e) {
                if ($e->getCode() == 0) {
                    $this->_messageManager->addError("Something went wrong while saving review attachment(s).");
                }
            }
        }
        return $observer;
    }
}
