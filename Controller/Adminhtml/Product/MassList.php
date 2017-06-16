<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use VIAeBay\Connector\Service\Backlog;
use VIAeBay\Connector\Service\Category;
use VIAeBay\Connector\Service\Product as ProductService;

class MassList extends Action
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Category
     */
    protected $categoryService;

    /**
     * @var ProductService
     */
    protected $productService;


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Backlog $backlogService
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ProductRepositoryInterface $productRepository,
        Category $categoryService,
        ProductService $productService
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->categoryService = $categoryService;
        $this->productService = $productService;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productsAddedToBacklog = 0;
        $this->categoryService->sync();
        foreach ($collection->getItems() as $product) {
            if ($product instanceof MagentoProduct) {
                $product->setData(ProductService::VIAEBAY_EXPORT_ATTRIBUTE, true);
                $product->getResource()->saveAttribute($product, ProductService::VIAEBAY_EXPORT_ATTRIBUTE);
                $this->productService->exportProductById($product->getId());
                $productsAddedToBacklog++;
            }
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 products(s) have been enabled for listing on VIA-eBay.', $productsAddedToBacklog)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/product/index');
    }
}
