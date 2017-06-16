<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Product extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('viaebay_product', 'viaebay_product_id');
    }

    /**
     * @param int $attributeId
     * @return array
     */
    public function loadByProductId(int $productId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable())
            ->where('product_id = :product_id')->limit(1, 0);
        $bind = ['product_id' => $productId];

        $return = $connection->fetchRow($select, $bind);
        return $return;
    }

}
