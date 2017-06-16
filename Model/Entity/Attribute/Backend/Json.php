<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\Entity\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;


/**
 * "Json" attribute backend
 */
class Json extends AbstractBackend
{
    /**
     * Encode before saving
     *
     * @param DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        // parent::beforeSave() is not called intentionally
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->hasData($attrCode)) {
            $object->setData($attrCode, json_encode($object->getData($attrCode)));
        }
        return $this;
    }

    /**
     * Decode after saving
     *
     * @param DataObject $object
     * @return $this
     */
    public function afterSave($object)
    {
        parent::afterSave($object);
        $this->jsonDecode($object);
        return $this;
    }

    /**
     * Decode after loading
     *
     * @param DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        parent::afterLoad($object);
        $this->jsonDecode($object);
        return $this;
    }

    /**
     * Try to encode the attribute value as json.
     *
     * @param DataObject $object
     * @return $this
     */
    protected function jsonDecode(DataObject $object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if ($object->getData($attrCode)) {
            $decoded = json_decode($object->getData($attrCode), true);

            if (json_last_error() != JSON_ERROR_NONE) {
                $object->setData($attrCode, $decoded);
            } else {
                $object->unsetData($attrCode);
            }
        }

        return $this;
    }
}
