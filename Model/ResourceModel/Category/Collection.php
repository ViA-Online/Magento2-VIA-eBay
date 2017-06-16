<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel\Category;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'viaebay_category_id';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\Category','VIAeBay\Connector\Model\ResourceModel\Category');
    }
}
