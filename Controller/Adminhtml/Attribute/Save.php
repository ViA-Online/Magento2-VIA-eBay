<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */
namespace VIAeBay\Connector\Controller\Adminhtml\Attribute;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use VIAeBay\Connector\Model\AttributeFactory as VIAAttributeFactory;
use VIAeBay\Connector\Model\AttributeRepository as VIAAttributeRepository;
use VIAeBay\Connector\Model\ResourceModel\Attribute as VIAResourceModelAttribute;

class Save extends Action
{
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
     * @param Context $context
     */
    public function __construct(Context $context, VIAAttributeRepository $attributeRepository,
                                VIAAttributeFactory $attributeFactory,
                                VIAResourceModelAttribute $attributeResourceModel)
    {
        $this->_attributeRepository = $attributeRepository;
        $this->_attributeFactory = $attributeFactory;
        $this->_attributeResourceModel = $attributeResourceModel;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ashsmith_Blog::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_attributeFactory->create();

            $id = $this->getRequest()->getParam('viaebay_attribute_id');
            if ($id) {
                $this->_attributeResourceModel->load($model, $id);
            }

            $model->setData($data);

            $this->_eventManager->dispatch(
                'viaebay_connector_attribute_save',
                ['attribute' => $model, 'request' => $this->getRequest()]
            );

            try {
                $this->_attributeResourceModel->save($model);
                $this->messageManager->addSuccessMessage(__('Attribute Mapping Saved.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['viaebay_attribute_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the attribute mapping.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['viaebay_attribute_id' => $this->getRequest()->getParam('post_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
