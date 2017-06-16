<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use VIAeBay\Connector\Service\Order;

class SingleImport extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Order
     */
    protected $orderService;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Order $orderService
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, Order $orderService)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->orderService = $orderService;
        return parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('viaebay_order_id');

        $this->orderService->import([$id]);

        $this->messageManager->addSuccessMessage(
            __('Order imported.')
        );

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('viaebay_connector/order/index');
        return $redirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VIAeBay_Connector::order');
    }
}
