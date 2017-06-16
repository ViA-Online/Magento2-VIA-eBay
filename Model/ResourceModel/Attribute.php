<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Attribute extends AbstractDb
{
    /**
     *
     */
    protected function _construct()
    {
        $this->_init('viaebay_attribute', 'viaebay_attribute_id');
    }

    /**
     * @param AbstractAttribute $attribute
     * @return array
     */
    public function loadByAttribute(AbstractAttribute $attribute)
    {
        return $this->loadByAttributeId($attribute->getId());

    }

    /**
     * @param int $attributeId
     * @return array
     */
    public function loadByAttributeId($attributeId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable())
            ->where('attribute_id = :attribute_id')->limit(1, 0);
        $bind = ['attribute_id' => $attributeId];

        $return = $connection->fetchAll($select, $bind);
        return $return;
    }

    /**
     * @param $attribute_code
     * @param string $entityTypeCode
     * @return array
     */
    public function loadByAttributeCode($attribute_code, $entityTypeCode = \Magento\Catalog\Model\Product::ENTITY)
    {
        $connection = $this->getConnection();

        $select = $connection->select('type')->from(['viaebay_attribute' => $this->getMainTable()])
            ->joinInner(['eav_attribute' => $this->getTable('eav_attribute')],
                'viaebay_attribute.attribute_id = eav_attribute.attribute_id', [])
            ->joinInner(['eav_entity_type' => $this->getTable('eav_entity_type')],
                'eav_attribute.entity_type_id = eav_entity_type.entity_type_id', [])
            ->where('eav_attribute.attribute_code = :attribute_code')
            ->where('eav_entity_type.entity_type_code = :entity_type_code');
        $bind = ['attribute_code' => $attribute_code, 'entity_type_code' => $entityTypeCode];

        $return = $connection->fetchAll($select, $bind);
        return $return;
    }
}
