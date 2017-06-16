<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Block\Adminhtml\Attribute;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'viaebay_attribute_id';
        $this->_controller = 'adminhtml_attribute';
        $this->_blockGroup = 'VIAeBay_Connector';

        parent::_construct();

        if ($this->_authorization->isAllowed('VIAeBay_Connector::attribute_save')) {
            $this->buttonList->update('save', 'label', __('Save Attribute Mapping'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_authorization->isAllowed('VIAeBay_Connector::attribute_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Attribute Mapping'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded news
     *
     * @return string
     */
    public function getHeaderText()
    {
        $viaebayAttribute = $this->coreRegistry->registry('viaebay_attribute')->getId();
        if ($viaebayAttribute->getId()) {
            return __("Edit Attribute Mapping '%1'", $this->escapeHtml($viaebayAttribute->getType));
        } else {
            return __('Add Attribute Mapping');
        }
    }
}
