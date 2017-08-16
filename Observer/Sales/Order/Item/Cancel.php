<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Observer\Sales\Order\Item;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Service\Product;

class Cancel implements ObserverInterface
{
    /**
     * @var Product
     */
    private $productService;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Product $productService, Logger $logger)
    {
        $this->productService = $productService;
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
            $item = $event->getData('item');

            if ($item == null || !($item instanceof OrderItemInterface)) {
                return;
            }

            $this->productService->updateStockById($item->getProductId());
        } catch (\Exception $exception) {
            $this->logger->addError("Cannot update stock\n" . $exception->__toString());
        }
    }
}
