<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Cron;

use Magento\Framework\ObjectManagerInterface;
use VIAeBay\Connector\Service\Order;

class OrderCronTask
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Order
     */
    private $orderService;

    /**
     * ImportCommand constructor.
     * @param ObjectManagerInterface $objectManager
     * @param Order $orderService
     */
    function __construct(ObjectManagerInterface $objectManager, Order $orderService)
    {
        $this->objectManager = $objectManager;
        $this->orderService = $orderService;
    }

    public function execute()
    {
        $this->objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');
        $this->orderService->import();
        return $this;
    }
}
