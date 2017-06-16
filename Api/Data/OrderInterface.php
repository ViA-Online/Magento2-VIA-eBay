<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Api\Data;

interface OrderInterface
{
    const VIAEBAY_ORDER_ID = 'viaebay_order_id';
    const MAGENTO_ORDER_ID = 'magento_order_id';
    const PLATFORM_ORDER_ID = 'platform_order_id';
    const BUYER_NAME = 'buyer_name';
    const MESSAGE = 'message';
    const ERROR = 'error';
    const CHECKOUT_COMPLETED_DATE = 'checkout_completed_date';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     * @return OrderInterface
     */
    public function setId($id);

    /**
     * @return mixed
     */
    function getMagentoOrderId();

    /**
     * @param $orderId
     * @return mixed
     */
    function setMagentoOrderId($orderId);

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getVIAeBayOrderId();

    /**
     * Set ID
     *
     * @param int $id
     * @return OrderInterface
     */
    public function setVIAeBayOrderId($id);

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getPlatformOrderId();

    /**
     * Set ID
     *
     * @param int $id
     * @return OrderInterface
     */
    public function setPlatformOrderId($id);


    /**
     * Get ID
     *
     * @return string|null
     */
    public function getBuyerName();

    /**
     * Set ID
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setBuyerName($id);

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getMessage();

    /**
     * Set ID
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setMessage($id);

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getError();

    /**
     * Set ID
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setError($id);

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getCheckoutCompletedDate();

    /**
     * Set ID
     *
     * @param string $id
     * @return OrderInterface
     */
    public function setCheckoutCompletedDate($id);

}