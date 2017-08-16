<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Observer\Checkout\Submit\All;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Service\Product;

class After implements ObserverInterface
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
            $order = $event->getData('order');
            $items = $order->getItems();

            if ($items == null) {
                return;
            }

            foreach ($items as $item) {
                if ($item instanceof OrderItemInterface) {
                    $this->productService->updateStockById($item->getProductId());
                }
            }
        } catch (\Exception $exception) {
            $this->logger->addError("Cannot update stock\n" . $exception->__toString());
        }
    }
}
