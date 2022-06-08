<?php
/**
 * Copyright 2020 Adorncommerce LLP. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Adorncommerce\ProductReviewRating\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Ratings
 * @package Adorncommerce\ProductReviewRating\Block
 */
class Ratings extends Template
{
    protected $_color = "";
    protected $_red = "red";
    protected $_orange = "orange";
    protected $_green = "green";
    protected $ratingFactory;
    protected $_reviewFactory;

    /**
     * Ratings constructor.
     * @param Template\Context $context
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewFactory,
        array $data = []
    ) {
        $this->ratingFactory = $ratingFactory;
        $this->_reviewFactory = $reviewFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param $avgVal
     * @return string
     */
    public function getClass($avgVal)
    {
        $pr = round((float)$avgVal);
        switch ($pr) {
            case ($pr <= 30):
                $this->_color = $this->_red;
                break;
            case ($pr <= 60):
                $this->_color = $this->_orange;
                break;
            case ($pr <= 100):
                $this->_color = $this->_green;
                break;
        }
        return $this->_color;
    }

    /**
     * @param $pid
     * @param $item
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllStart($pid, $item)
    {
        $arrRatings = array();
        $review = $this->_reviewFactory->create()
            ->addFieldToFilter('main_table.status_id', 1)
            ->addEntityFilter('product', $pid)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addFieldToSelect('review_id');
        $review->getSelect()->columns('detail.detail_id')->joinInner(
            ['vote' => $review->getTable('rating_option_vote')],
            'main_table.review_id = vote.review_id',
            ['review_value' => 'vote.value', 'rating_id' => 'vote.rating_id']
        );
        $review->getSelect()->columns('sum(vote.percent) as total_percent')->group('rating_id');
        if ($review->getData()) {
            $rating = array_column($review->getData(), 'total_percent','rating_id');
            foreach ($rating as $key => $_result) {
                $ratingData = $this->ratingFactory->create()->load($key);
                $arrRatings[$ratingData->getRatingCode()]['total_percent'] = $_result;
                $percent = $_result / count($item);
                $arrRatings[$ratingData->getRatingCode()]['percent'] = round($percent, 0);
                $arrRatings[$ratingData->getRatingCode()]['average'] =round((float)($percent * 5) / 100, 2);
            }
        } else {
            $label = $this->ratingFactory->create()->getResourceCollection()->addEntityFilter(
                'product'
            )->setPositionOrder()->addRatingPerStoreName(
                $this->_storeManager->getStore()->getId()
            )->setStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->setActiveFilter(
                true
            )->load()->addOptionToItems();
            foreach ($label as $value) {
                $arrRatings[$value->getRatingCode()]['total_percent'] = 0;
                $arrRatings[$value->getRatingCode()]['percent'] = 0;
                $arrRatings[$value->getRatingCode()]['average'] = 0;
            }
        }
        return $arrRatings;
    }
}
