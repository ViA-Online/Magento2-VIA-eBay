<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Observer\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Service\Product;

class Delete implements ObserverInterface
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

            $product = $event->getData('product');

            if ($product == null || !($product instanceof ProductInterface)) {
                return;
            }

            $this->productService->deleteProduct($product);
        } catch (\Exception $exception) {
            $this->logger->addError("Could not delete product\n" . $exception->__toString());
        }
    }
}
