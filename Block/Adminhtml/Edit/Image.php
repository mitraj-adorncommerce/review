<?php
/**
 * Image
 *
 * @copyright Copyright © 2021 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Adorncommerce\ProductReviewRating\Block\Adminhtml\Edit;


class Image extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory
    )
    {
        $this->reviewFactory = $reviewFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Review\Model\ResourceModel\Review\Collection|void
     */
    public function getReviewData()
    {
        $reviewData = $this->reviewFactory->create()->load($this->getRequest()->getParam('id'));

        return $reviewData;
    }

    /**
     * function
     * get review_images directory path
     *
     * @return string
     */
    public function getReviewMediaUrl()
    {
        $reviewMediaDirectoryPath = $this->_storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'product_review_image';

        return $reviewMediaDirectoryPath;
    }

}
