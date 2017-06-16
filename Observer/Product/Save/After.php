<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Observer\Product\Save;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Service\Backlog;

class After implements ObserverInterface
{
    /**
     * @var Backlog
     */
    private $backlog;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Backlog $backlog, Logger $logger)
    {
        $this->backlog = $backlog;
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

            $product = $event->getData('product');

            if ($product == null || !($product instanceof ProductInterface)) {
                return;
            }

            $this->backlog->createBacklog($product);
        } catch (\Exception $exception) {
            $this->logger->addError("Cannot create backlog\n" . $exception->__toString());
        }
    }
}
