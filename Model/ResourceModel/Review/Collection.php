<?php
/**
 * Copyright 2020 Adorncommerce LLP. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Adorncommerce\ProductReviewRating\Model\ResourceModel\Review;

/**
 * Class Collection
 * @package Adorncommerce\ProductReviewRating\Model\ResourceModel\Review
 */
class Collection extends \Magento\Review\Model\ResourceModel\Review\Collection
{
    protected $_reviewDetailTable;
    public function __construct(\Magento\Framework\Data\Collection\EntityFactory $entityFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Review\Helper\Data $reviewData, \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\DB\Adapter\AdapterInterface $connection = null, \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null)
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $reviewData, $voteFactory, $storeManager, $connection, $resource);
    }
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->join(
            ['details' => $this->getReviewDetailTable()],
            'main_table.review_id = details.review_id',
            ['detail_id', 'title', 'detail', 'nickname', 'customer_id', 'admin_reply', 'image']
        );
        return $this;
    }
}
