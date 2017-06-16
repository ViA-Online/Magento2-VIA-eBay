<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductVariation extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('viaebay_product_variation','viaebay_product_variation_id');
    }

    /**
     * Load an object
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     */
    public function loadByProductIds(AbstractModel $object, $parentProductId, $childProductId)
    {
        $connection = $this->getConnection();
        if ($connection) {
            $select = $this->getConnection()->select()->from(['pv' => $this->getMainTable()])->where('pv.product_id =?', $childProductId);
            $select->joinInner(['vp' => $this->getTable('viaebay_product')],
                'vp.viaebay_product_id = pv.viaebay_product_id', []);
            $select->where('vp.product_id =?', $parentProductId);

            $data = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }
}
