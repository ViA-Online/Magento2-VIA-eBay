<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\Backlog;
class Product extends \Magento\Framework\Model\AbstractModel implements ProductInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'viaebay_backlog_product';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\ResourceModel\Backlog\Product');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
