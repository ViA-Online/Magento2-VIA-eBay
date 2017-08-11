<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Plugin\Sales\Model\ResourceModel\Order\Shipment\Track;

use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track as TrackResource;
use VIAeBay\Connector\Helper\Configuration;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Service\Order;


/**
 *
 * Plugin used to a afterSave with new object detection.
 * Class Plugin
 * @package VIAeBay\Connector\Plugin\ResourceModel\Sales\Order\Shipment\Track
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

    public function aroundSave(TrackResource $subject, \Closure $proceed, AbstractModel $interceptedInput)
    {
        $isNew = $interceptedInput->isObjectNew();

        // Call requested method
        $result = $proceed($interceptedInput);

        try {
            if ($this->configurationHelper->isActive() && $isNew && $interceptedInput instanceof Track) {
                $this->orderService->addTrackingNumber($interceptedInput);
            }
        } catch (\Exception $exception) {
            $this->logger->addError("Could not add tracking number\n" . $exception->__toString());
        }

        return $result;
    }
}