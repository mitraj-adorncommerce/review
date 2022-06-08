<?php
/**
 * Copyright 2020 Adorncommerce LLP. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Adorncommerce\ProductReviewRating\Plugin\Adminhtml\Add;

/**
 * Class Form
 * @package Adorncommerce\ProductReviewRating\Plugin\Adminhtml\Add
 */
class Form
{
    protected $helperData;

    public function __construct(\Adorncommerce\ProductReviewRating\Helper\Data $helperData)
    {
        $this->helperData = $helperData;
    }

    public function beforeSetForm(\Magento\Review\Block\Adminhtml\Add\Form $object, $form)
    {
        if (!$this->helperData->isModuleEnabled()) {
            return [$form];
        }
        $fieldset = $form->addFieldset(
            'review_details_extra',
            ['legend' => __(''), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'admin_reply',
            'textarea',
            ['label' => __('Admin Reply'), 'required' => false, 'name' => 'admin_reply']
        );
        $fieldset->addField(
            'notify_customer',
            'checkbox',
            [
                'label' => __('Notify Customer'),
                'name' => 'notify_customer',
                'onchange' => 'this.value = this.checked;'
            ]
        );
        return [$form];
    }
}
