<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Plugin\Sales\Model\ResourceModel\Order\Shipment;

use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use VIAeBay\Connector\Helper\Configuration;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Service\Order;

/**
 * Plugin used to a afterSave with new object detection.
 * Class Plugin
 * @package VIAeBay\Connector\Plugin\ResourceModel\Sales\Order\Shipment
 */
class Plugin
{
    /**
     * @var Order
     */
    private $orderService;

    /**
     * @var Configuration
     */
    private $configurationHelper;

    /**
     * @var Logger
     */
    private $logger;

    function __construct(Order $orderService, Configuration $configuration, Logger $logger)
    {
        $this->orderService = $orderService;
        $this->configurationHelper = $configuration;
        $this->logger = $logger;
    }

    public function aroundSave(ShipmentResource $subject, \Closure $proceed, AbstractModel $interceptedInput)
    {
        $isNew = $interceptedInput->isObjectNew();

        // Call requested method
        $result = $proceed($interceptedInput);

        try {
            if ($this->configurationHelper->isActive() && $isNew && $interceptedInput instanceof Shipment) {
                $this->orderService->updateShipmentStatus($interceptedInput);
            }
        }catch (\Exception $exception) {
            $this->logger->addError("Could not update shipment status\n" . $exception->__toString());
        }

        return $result;
    }
}