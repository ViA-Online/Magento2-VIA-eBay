<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use VIAeBay\Connector\Api\Data\AttributeInterface;

class Attribute extends AbstractModel implements AttributeInterface, IdentityInterface
{
    const CACHE_TAG = 'viaebay_attribute';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\ResourceModel\Attribute');
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
    public function getAttributeId()
    {
        $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return AttributeInterface
     */
    public function setAttributeId($id)
    {
        $this->setData(self::ATTRIBUTE_ID, $id);
        return $this;
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVIAeBayAttributeId()
    {
        return $this->getId();
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return AttributeInterface
     */
    public function setVIAeBayAttributeId($id)
    {
        return $this->setId($id);
    }

    /**
     * Get Type
     * @return string|null
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Set Type
     *
     * @param string|null $type
     * @return AttributeInterface
     */
    public function setType($type)
    {
        $this->setData(self::TYPE, $type);
        return $this;
    }
}
