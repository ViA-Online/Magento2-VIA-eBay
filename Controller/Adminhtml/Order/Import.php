<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use VIAeBay\Connector\Service\Order as OrderService;

class Import extends Action
{
    /**
     * @var OrderService
     */
    protected $_orderService;

    /**
     * Import constructor.
     * @param Context $context
     * @param OrderService $order
     */
    public function __construct(Context $context, OrderService $order)
    {
        $this->_orderService = $order;
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->_orderService->import();

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('viaebay_connector/order/index');
        return $redirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VIAeBay_Connector::order');
    }

}
