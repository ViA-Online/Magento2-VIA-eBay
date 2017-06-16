<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Controller\Adminhtml\Backlog\Product;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use VIAeBay\Connector\Service\Category;
use VIAeBay\Connector\Service\Product;

class Process extends Action
{
    /**
     * @var Category
     */
    protected $_category;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * Process constructor.
     * @param Context $context
     * @param Category $category
     * @param Product $product
     */
    public function __construct(Context $context, Category $category, Product $product)
    {
        $this->_category = $category;
        $this->_product = $product;
        return parent::__construct($context);
    }

    public function execute()
    {
        $this->_category->sync();
        $this->_product->exportProducts();

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setPath('viaebay_connector/backlog_product/index');
        return $redirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VIAeBay_Connector::backlog');
    }
}
