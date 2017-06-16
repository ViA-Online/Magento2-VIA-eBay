<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use VIAeBay\Connector\Api\Data\ProductVariationInterface;

class ProductVariation extends AbstractModel implements ProductVariationInterface, IdentityInterface
{
    const CACHE_TAG = 'viaebay_connector_productvariation';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\ResourceModel\ProductVariation');
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
     * @return ProductVariationInterface
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
    public function getVIAeBayProductVariationId()
    {
        return $this->getData(self::VIAEBAY_PRODUCT_VARIATION_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return ProductVariationInterface
     */
    public function setVIAeBayProductVariationId($id)
    {
        $this->setData(self::VIAEBAY_PRODUCT_VARIATION_ID, $id);
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
     * @return ProductVariationInterface
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
     * @return ProductVariationInterface
     */
    public function setVIAeBayId($id)
    {
        $this->setData(self::VIAEBAY_ID, $id);
        return $this;
    }
}
