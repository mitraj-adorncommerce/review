<?php
/**
 * Copyright 2020 Adorncommerce LLP. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Adorncommerce\ProductReviewRating\Block;

use Magento\Framework\View\Element\Template;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory;
use Magento\Setup\Exception;

/**
 * Class ProductReviews
 * @package Adorncommerce\ProductReviewRating\Block
 */
class ProductReviews extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $_reviewFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Review\Model\Rating
     */
    protected $_ratingFactory;

    protected $_messgeManager;
    /**
     * @var
     */
    protected $_color;
    protected $_red = "red";
    protected $_orange = "orange";
    protected $_green = "green";

    /**
     * ProductReviews constructor.
     * @param Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CollectionFactory $reviewFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $reviewFactory,
        \Magento\Review\Model\Rating $ratingFactory,
        \Magento\Framework\Message\ManagerInterface $messgeManager,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_messgeManager = $messgeManager;
        parent::__construct($context, $data);
    }

    /**
     * @param $percent
     * @return string
     */
    public function getClass($percent)
    {
        $pr = round((float)$percent);
        switch (round((float)$percent)) {
            case ($pr <= 30):
                $this->_color = $this->_red;
                break;
            case ($pr <= 60):
                $this->_color = $this->_orange;
                break;
            case ($pr <= 100):
                $this->_color = $this->_green;
                break;
            default:
                $this->_color = '';
        }
        return $this->_color;
    }

    /**
     * @param $product_id
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPercentage($product_id)
    {
        $_ratingSummary = $this->_ratingFactory->getEntitySummary($product_id);
        $ratingCollection = $this->_reviewFactory->create()
            ->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $product_id);
        $review_count = count($ratingCollection);
        if ($review_count) {
            $product_rating = $_ratingSummary->getSum() / $_ratingSummary->getCount();
        } else {
            $product_rating = 0;
        }
        return $product_rating;
    }

    /**
     * @param $productId
     * @return float|int
     */
    public function getAverage($productId)
    {
        $rating = $this->getPercentage($productId);
        $avrage = (float)($rating * 5) / 100;
        return $avrage;
    }

    /**
     * @param $pid
     * @return array|\Magento\Framework\Message\ManagerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllStart($pid)
    {
        $_ratingSummary = $this->_ratingFactory->getEntitySummary($pid);
        $review = $this->_reviewFactory->create()
            ->addFieldToFilter('main_table.status_id', 1)
            ->addEntityFilter('product', $pid)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addFieldToSelect('review_id');

        $review->getSelect()->columns('detail.detail_id')->joinInner(
            ['vote' => $review->getTable('rating_option_vote')],
            'main_table.review_id = vote.review_id',
            ['review_value' => 'vote.value']
        );
        $review->getSelect()->columns('count(vote.vote_id) as total_vote');
        $review->getSelect()->columns('sum(vote.percent) as total_percent');
        $review->getSelect()->group('vote.review_id');

        for ($i = 5; $i >= 1; $i--) {
            $arrRatings[$i]['value'] = 0;
        }
        foreach ($review as $_result) {
            if ($_result['total_vote']){
                $torat = $_result['total_percent'] / $_result['total_vote'];
            }else{
                $torat = 0;
            }
            switch ($torat) {
                case ($torat <= 100 && $torat > 80):
                    $arrRatings[5]['value'] += 1;
                    break;
                case ($torat <= 80 && $torat > 60):
                    $arrRatings[4]['value'] += 1;
                    break;
                case ($torat <= 60 && $torat > 40):
                    $arrRatings[3]['value'] += 1;
                    break;
                case ($torat <= 40 && $torat > 20):
                    $arrRatings[2]['value'] += 1;
                    break;
                case ($torat <= 20 && $torat > 0):
                    $arrRatings[1]['value'] += 1;
                    break;
            }
        }
        return $arrRatings;
    }
}
