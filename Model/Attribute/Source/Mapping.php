<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\Attribute\Source;


class Mapping extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('None'), 'value' => 'None', 'unique' => false],
                ['label' => __('Custom Attribute'), 'value' => 'CustomAttribute', 'unique' => false],
                ['label' => __('Optional Attribute'), 'value' => 'OptionalAttribute', 'unique' => false],
                ['label' => __('Title'), 'value' => 'Title', 'unique' => true],
                ['label' => __('Price'), 'value' => 'Price', 'unique' => true],
                ['label' => __('Discount Offer'), 'value' => 'DiscountOffer', 'unique' => true],
                ['label' => __('Description'), 'value' => 'Description', 'unique' => true],
                ['label' => __('Short Description'), 'value' => 'ShortDescription', 'unique' => true],
                ['label' => __('Ean'), 'value' => 'Ean', 'unique' => true],
                ['label' => __('Mpn'), 'value' => 'Mpn', 'unique' => true],
                ['label' => __('Brand'), 'value' => 'Brand', 'unique' => true],
                ['label' => __('Upc'), 'value' => 'Upc', 'unique' => true],
                ['label' => __('Isbn'), 'value' => 'Isbn', 'unique' => true],
                ['label' => __('Unit Quantity'), 'value' => 'UnitQuantity', 'unique' => true],
                ['label' => __('Unit Type'), 'value' => 'UnitType', 'unique' => true],
                ['label' => __('KType'), 'value' => 'KType', 'unique' => true],
                ['label' => __('HSN'), 'value' => 'HSN', 'unique' => true],
                ['label' => __('TSN'), 'value' => 'TSN', 'unique' => true]
            ];
        }
        return $this->_options;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $_options = [];
        foreach ($this->getAllOptions() as $option) {
            $_options[$option['value']] = $option['label'];
        }
        return $_options;
    }
}