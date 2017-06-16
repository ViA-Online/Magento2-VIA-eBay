<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Cron;

use Magento\Framework\ObjectManagerInterface;
use VIAeBay\Connector\Service\Category;
use VIAeBay\Connector\Service\Product;

class BacklogCronTask
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var Category
     */
    protected $categoryService;

    /**
     * @var Product
     */
    protected $productService;

    public function __construct(ObjectManagerInterface $objectManager, Category $order, Product $product)
    {
        $this->objectManager = $objectManager;
        $this->categoryService = $order;
        $this->productService = $product;
    }

    public function execute()
    {
        $this->objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');
        $this->categoryService->sync();
        $this->productService->exportProducts();
        return $this;
    }

}