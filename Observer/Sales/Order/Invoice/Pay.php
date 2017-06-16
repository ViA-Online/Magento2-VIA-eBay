<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Observer\Sales\Order\Invoice;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Service\Order;

class Pay implements ObserverInterface
{
    /**
     * @var Order
     */
    private $orderService;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Order $orderService, Logger $logger)
    {
        $this->orderService = $orderService;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();

            $invoice = $event->getData('invoice');

            if ($invoice == null || !($invoice instanceof InvoiceInterface)) {
                return;
            }

            $this->orderService->updatePaid($invoice);
        } catch (\Exception $exception) {
            $this->logger->addError("Cannot update payment\n" . $exception->__toString());
        }
    }
}
