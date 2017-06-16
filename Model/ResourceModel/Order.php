<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Order extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('viaebay_order','viaebay_order_id');
        $this->_useIsObjectNew = true;
        $this->_isPkAutoIncrement = false;
    }
}
