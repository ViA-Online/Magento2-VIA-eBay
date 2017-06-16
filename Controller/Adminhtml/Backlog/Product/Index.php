<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Controller\Adminhtml\Backlog\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $page->setActiveMenu('VIAeBay_Connector::backlog');
        $page->getConfig()->getTitle()->prepend(__('Backlog (Product)'));
        return $page;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VIAeBay_Connector::backlog');
    }
}
