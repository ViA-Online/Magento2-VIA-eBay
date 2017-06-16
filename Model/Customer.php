<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use VIAeBay\Connector\Api\Data\CustomerInterface;

class Customer extends AbstractModel implements CustomerInterface, IdentityInterface
{
    const CACHE_TAG = 'viaebay_connector_customer';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\ResourceModel\Customer');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
