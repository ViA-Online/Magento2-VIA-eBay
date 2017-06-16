<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Block\Adminhtml\Attribute\Edit;


use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use VIAeBay\Connector\Model\Attribute\Source\Mapping as AttributeSource;

class Form extends Generic
{
    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var AttributeSource
     */
    protected $_attributeSource;

    /**
     * @var AttributeRepository
     */
    protected $_attributeRepository;

    /**
     * @var SearchCriteriaFactory
     */
    protected $_searchCriteriaFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, FormFactory $formFactory, Store $systemStore,
                                AttributeRepository $attributeRepository, AttributeSource $attributeSource,
                                SearchCriteriaFactory $searchCriteriaFactory, array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_attributeRepository = $attributeRepository;
        $this->_attributeSource = $attributeSource;
        $this->_searchCriteriaFactory = $searchCriteriaFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('viaebay_attribute_form');
        $this->setTitle(__('VIA-eBay Attribute Mapping'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \VIAeBay\Connector\Model\Attribute $attribute */
        $attribute = $this->_coreRegistry->registry('viaebay_attribute');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('post_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information'), 'class' => 'fieldset-wide']
        );

        if ($attribute->getData('viaebay_attribute_id')) {
            $fieldset->addField('viaebay_attribute_id', 'hidden', ['name' => 'viaebay_attribute_id']);
        }

        $fieldset->addField(
            'attribute_id',
            'select',
            [
                'label' => __('Magento Attribute'),
                'title' => __('Magento Attribute'),
                'name' => 'attribute_id',
                'required' => true,
                'options' => $this->getMagentoAttributeOptions()
            ]
        );

        $fieldset->addField(
            'type',
            'select',
            [
                'label' => __('VIA-eBay Attribute'),
                'title' => __('VIA-eBay Attribute'),
                'name' => 'type',
                'required' => true,
                'options' => $this->_attributeSource->getOptionArray()
            ]
        );

        $form->setValues($attribute->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    protected function getMagentoAttributeOptions()
    {
        $criteria = $this->_searchCriteriaFactory->create();
        $attributes = $this->_attributeRepository->getList(Product::ENTITY, $criteria);

        $result = [];
        foreach ($attributes->getItems() as $attribute) {
            /** @var Attribute $attribute */
            $result[$attribute->getId()] =  $attribute->getDefaultFrontendLabel() . ' (' . $attribute->getAttributeCode() . ')';
        }
        return $result;
    }
}
