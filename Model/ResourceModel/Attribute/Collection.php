<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel\Attribute;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'viaebay_attribute_id';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\Attribute','VIAeBay\Connector\Model\ResourceModel\Attribute');
    }
}
