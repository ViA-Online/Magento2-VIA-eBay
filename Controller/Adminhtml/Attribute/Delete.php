<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */
namespace VIAeBay\Connector\Controller\Adminhtml\Attribute;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use VIAeBay\Connector\Model\AttributeRepository as VIAAttributeRepository;
use VIAeBay\Connector\Model\ResourceModel\Attribute as VIAResourceModelAttribute;


class Delete extends Action
{
    /**
     * @var VIAAttributeRepository
     */
    protected $_attributeRepository;

    /**
     * Index constructor.
     * @param Context $context
     * @param VIAAttributeRepository $attributeRepository
     */
    public function __construct(Context $context, VIAAttributeRepository $attributeRepository)
    {
        $this->_attributeRepository = $attributeRepository;
        return parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('viaebay_attribute_id');
        if ($id) {
            try {
                $this->_attributeRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('The attribute mapping has been deleted.'));
                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->_redirect('*/*/edit', ['viaebay_attribute_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a attribute mapping to delete.'));
        return $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VIAeBay_Connector::attribute');
    }
}
