<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Observer\CatalogInventory\Stock\Item\Save;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
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
            $item = $event->getData('item');

            if ($item == null && !$item instanceof StockItemInterface) {
                return;
            }

            $this->productService->updateStockById($item->getProductId());
        } catch (\Exception $exception) {
            $this->logger->addError("Cannot update stock\n" . $exception->__toString());
        }
    }
}
