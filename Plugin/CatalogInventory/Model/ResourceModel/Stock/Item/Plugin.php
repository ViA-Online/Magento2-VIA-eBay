<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Plugin\CatalogInventory\Model\ResourceModel\Stock\Item;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as StockItemResource;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Service\Product;

/**
 * Plugin used to detect stock changes. This could have been an observer but observers are not working due to
 * MAGETWO-54066.
 * Class Plugin
 * @package VIAeBay\Connector\Plugin
 */
class Plugin
{
    /**
     * @var Product
     */
    private $productService;

    /**
     * @var Logger
     */
    private $logger;

    function __construct(Product $product, Logger $logger)
    {
        $this->productService = $product;
        $this->logger = $logger;
    }

    public function aroundSave(StockItemResource $subject, \Closure $proceed, $interceptedInput)
    {
        // Call requested method
        $result = $proceed($interceptedInput);

        try {
            if ($interceptedInput instanceof StockItemInterface) {
                $this->productService->updateStockById($interceptedInput->getProductId(), $interceptedInput->getQty());
            }
        } catch (\Exception $exception) {
            $this->logger->addError("Failed to update stock\n" . $exception->__toString());
        }

        return $result;
    }
}