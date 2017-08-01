<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Cron;

use VIAeBay\Connector\Helper\State;
use VIAeBay\Connector\Service\Category;
use VIAeBay\Connector\Service\Product;

class BacklogCronTask
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var Category
     */
    protected $categoryService;

    /**
     * @var Product
     */
    protected $productService;

    /**
     * BacklogCronTask constructor.
     * @param State $state
     * @param Category $order
     * @param Product $product
     */
    public function __construct(State $state, Category $order, Product $product)
    {
        $this->state = $state;
        $this->categoryService = $order;
        $this->productService = $product;
    }

    public function execute()
    {
        $this->state->initializeAreaCode();

        $this->categoryService->sync();
        $this->productService->exportProducts();
        return $this;
    }

}