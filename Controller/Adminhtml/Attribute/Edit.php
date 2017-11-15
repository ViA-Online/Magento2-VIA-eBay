<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use VIAeBay\Connector\Model\AttributeFactory as VIAAttributeFactory;
use VIAeBay\Connector\Model\AttributeRepository as VIAAttributeRepository;
use VIAeBay\Connector\Model\ResourceModel\Attribute as VIAResourceModelAttribute;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var VIAAttributeRepository
     */
    protected $_attributeRepository;

    /**
     * @var VIAAttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var VIAResourceModelAttribute
     */
    protected $_attributeResourceModel;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Session $session
     * @param VIAAttributeRepository $attributeRepository
     * @param VIAAttributeFactory $attributeFactory
     * @param VIAResourceModelAttribute $attributeResourceModel
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, Registry $registry,
                                VIAAttributeRepository $attributeRepository, VIAAttributeFactory $attributeFactory,
                                VIAResourceModelAttribute $attributeResourceModel)
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->_attributeRepository = $attributeRepository;
        $this->_attributeFactory = $attributeFactory;
        $this->_attributeResourceModel = $attributeResourceModel;
        return parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('VIAeBay_Connector::attribute')
            ->addBreadcrumb(__('VIA-Connect Attribute'), __('VIA-Connect Attribute'))
            ->addBreadcrumb(__('Manage VIA-Connect Attribute'), __('Manage VIA-Connect Attribute'));
        return $resultPage;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('viaebay_attribute_id');
        $attribute = $this->_attributeFactory->create();

        if ($id) {
            $this->_attributeResourceModel->load($attribute, $id);

            if (!$attribute->getId()) {
                $this->messageManager->addErrorMessage(__('This attribute mapping no longer exists.'));
                return $this->_redirect('*/*/');
            }
        }

        $data = $this->_session->getFormData(true);
        if (!empty($data)) {
            $attribute->setData($data);
        }

        $this->_coreRegistry->register('viaebay_attribute', $attribute);

        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Attribute Mapping') : __('New Attribute Mapping'),
            $id ? __('Edit Attribute Mapping') : __('New Attribute Mapping')
        );

        $title = $resultPage->getConfig()->getTitle();
        $title->prepend(__('Attribute Mappings'));
        $title->prepend($attribute->getId() ? __('Edit Attribute Mapping') : __('New Attribute Mapping'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VIAeBay_Connector::attribute');
    }
}
