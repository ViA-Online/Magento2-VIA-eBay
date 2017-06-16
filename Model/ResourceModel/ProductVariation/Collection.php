<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel\ProductVariation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\ProductVariation','VIAeBay\Connector\Model\ResourceModel\ProductVariation');
    }
}
