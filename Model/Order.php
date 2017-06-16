<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use VIAeBay\Connector\Api\Data\OrderInterface;

class Order extends AbstractModel implements OrderInterface, IdentityInterface
{
    const CACHE_TAG = 'viaebay_connector_order';

    protected function _construct()
    {
        $this->_init('VIAeBay\Connector\Model\ResourceModel\Order');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setObjectNew($value = true)
    {
        $this->_isObjectNew = $value;
    }

    /**
     * @return string
     */
    function getMagentoOrderId()
    {
        return $this->getData(self::MAGENTO_ORDER_ID);
    }

    function setMagentoOrderId($orderId)
    {
        $this->setData(self::MAGENTO_ORDER_ID, $orderId);
    }

    /**
     * @return string
     */
    function getVIAeBayOrderId()
    {
        return $this->getData(self::VIAEBAY_ORDER_ID);
    }

    function setVIAeBayOrderId($orderId)
    {
        $this->setData(self::VIAEBAY_ORDER_ID, $orderId);
    }


    /**
     * Get ID
     *
     * @return int|null
     */
    public function getPlatformOrderId()
    {
        return $this->getData(self::PLATFORM_ORDER_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return OrderInterface
     */
    public function setPlatformOrderId($id)
    {
        $this->setData(self::PLATFORM_ORDER_ID, $id);
        return $this;
    }

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getBuyerName()
    {
        return $this->getData(self::BUYER_NAME);
    }

    /**
     * Set ID
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setBuyerName($id)
    {
        $this->setData(self::BUYER_NAME, $id);
        return $this;
    }

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Set ID
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setMessage($id)
    {
        $this->setData(self::MESSAGE, $id);
        return $this;
    }

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->getData(self::ERROR);
    }

    /**
     * Set ID
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setError($id)
    {
        $this->setData(self::ERROR, $id);
        return $this;
    }

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getCheckoutCompletedDate()
    {
        return $this->getData(self::CHECKOUT_COMPLETED_DATE);
    }

    /**
     * Set ID
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setCheckoutCompletedDate($id)
    {
        $this->setData(self::CHECKOUT_COMPLETED_DATE, $id);
        return $this;
    }
}
