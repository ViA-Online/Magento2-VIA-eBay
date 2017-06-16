<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel\Product;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'viaebay_product_id';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\Product','VIAeBay\Connector\Model\ResourceModel\Product');
    }
}
