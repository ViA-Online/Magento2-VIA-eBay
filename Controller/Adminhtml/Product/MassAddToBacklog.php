<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use VIAeBay\Connector\Api\Backlog\ProductRepositoryInterface;
use VIAeBay\Connector\Service\Backlog;

class MassAddToBacklog extends Action
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
     * @var Backlog
     */
    protected $backlogService;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Backlog $backlog
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Backlog $backlog
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->backlogService = $backlog;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productsAddedToBacklog = 0;
        foreach ($collection->getItems() as $product) {
            if ($product instanceof ProductInterface) {
                $this->backlogService->createBacklog($product);
                $productsAddedToBacklog++;
            }
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 products(s) have been added to backlog.', $productsAddedToBacklog)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/product/index');
    }
}
