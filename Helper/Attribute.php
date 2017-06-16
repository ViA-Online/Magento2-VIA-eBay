<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Helper;

use Magento\Eav\Api\Data\AttributeExtensionInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\ExtensionAttributesFactory;

class Attribute
{
    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * Attribute constructor.
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(ExtensionAttributesFactory $extensionAttributesFactory)
    {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * @param AttributeInterface $attribute
     * @return AttributeExtensionInterface|null
     */
    public function getExtensionAttributes(AttributeInterface $attribute)
    {
        /* @var $attribute AbstractAttribute */

        $attributes = $attribute->getExtensionAttributes();

        if ($attributes == null) {
            /* @var $attributes AttributeExtensionInterface */
            $attributes = $this->extensionAttributesFactory->create('Magento\Eav\Api\Data\AttributeInterface');
            $attribute->setExtensionAttributes($attributes);
        }

        return $attributes;
    }
}
