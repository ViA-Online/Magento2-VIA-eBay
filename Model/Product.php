<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use VIAeBay\Connector\Api\Data\ProductInterface;

class Product extends AbstractModel implements ProductInterface, IdentityInterface
{
    const CACHE_TAG = 'viaebay_connector_product';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\ResourceModel\Product');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return ProductInterface
     */
    public function setProductId($id)
    {
        $this->setData(self::PRODUCT_ID, $id);
        return $this;
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVIAeBayProductId()
    {
        return $this->getData(self::VIAEBAY_PRODUCT_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return ProductInterface
     */
    public function setVIAeBayProductId($id)
    {
        $this->setData(self::VIAEBAY_PRODUCT_ID, $id);
        return $this;
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVIAeBayId()
    {
        return $this->getData(self::VIAEBAY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return ProductInterface
     */
    public function setVIAeBayId($id)
    {
        $this->setData(self::VIAEBAY_ID, $id);
        return $this;
    }
}
