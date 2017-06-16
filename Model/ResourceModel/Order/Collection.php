<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel\Order;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'viaebay_order_id';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\Order','VIAeBay\Connector\Model\ResourceModel\Order');
    }
}
