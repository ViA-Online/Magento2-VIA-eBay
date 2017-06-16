<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use VIAeBay\Connector\Api\OrderRepositoryInterface;
use VIAeBay\Connector\Model\ResourceModel\Order\CollectionFactory;
use VIAeBay\Connector\Service\Order;

class MassImport extends Action
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
     * @var Order
     */
    protected $orderService;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Order $orderService
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->orderService = $orderService;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $orderImported = 0;

        $ids = [];
        foreach ($collection->getItems() as $order) {
            $ids[] = $order->getId();
            $orderImported++;
        }
        $this->orderService->import($ids);

        $this->messageManager->addSuccessMessage(
            __('A total of %1 orders(s) have been imported.', $orderImported)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('viaebay_connector/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VIAeBay_Connector::order');
    }

}
