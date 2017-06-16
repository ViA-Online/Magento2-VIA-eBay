<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use VIAeBay\Connector\Api\Data\CategoryInterface;

class Category extends AbstractModel implements CategoryInterface, IdentityInterface
{
    const CACHE_TAG = 'viaebay_connector_category';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\ResourceModel\Category');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
