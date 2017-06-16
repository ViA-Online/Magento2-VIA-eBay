<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel\Backlog\Product;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'viaebay_backlog_product_id';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\Backlog\Product','VIAeBay\Connector\Model\ResourceModel\Backlog\Product');
    }
}
