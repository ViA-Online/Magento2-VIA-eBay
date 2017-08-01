<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Cron;

use VIAeBay\Connector\Helper\State;
use VIAeBay\Connector\Service\Order;

class OrderCronTask
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var Order
     */
    private $orderService;

    /**
     * ImportCommand constructor.
     * @param State $state
     * @param Order $orderService
     */
    function __construct(State $state, Order $orderService)
    {
        $this->state = $state;
        $this->orderService = $orderService;
    }

    public function execute()
    {
        $this->state->initializeAreaCode();

        $this->orderService->import();
        return $this;
    }
}
