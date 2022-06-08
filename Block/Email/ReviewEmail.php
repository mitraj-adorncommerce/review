<?php
/**
 * Copyright 2020 Adorncommerce LLP. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Adorncommerce\ProductReviewRating\Block\Email;
/**
 * Class ReviewEmail
 * @package Adorncommerce\ProductReviewRating\Block\Email
 */
class ReviewEmail extends \Magento\Framework\View\Element\Template
{
    /**
     * @var null
     */
    protected $_reviewsCollection = null;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * ReviewEmail constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        array $data = []
    ) {
        $this->_reviewFactory = $reviewFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param $reviewId
     * @return \Magento\Review\Model\Review|null
     */
    public function getReviewsCollection($reviewId)
    {
        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = $this->_reviewFactory->create()->load($reviewId);
        }
        return $this->_reviewsCollection;
    }
}
