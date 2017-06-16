<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel\Customer;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'viaebay_customer_id';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\Customer','VIAeBay\Connector\Model\ResourceModel\Customer');
    }
}
