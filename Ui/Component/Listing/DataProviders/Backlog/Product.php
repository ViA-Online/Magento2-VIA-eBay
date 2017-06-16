<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */
namespace VIAeBay\Connector\Ui\Component\Listing\DataProviders\Backlog;

use Magento\Ui\DataProvider\AbstractDataProvider;
use VIAeBay\Connector\Model\ResourceModel\Backlog\Product\CollectionFactory;

class Product extends AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collection->join(
            $this->collection->getTable('catalog_product_entity_varchar'),
            'catalog_product_entity_varchar.entity_id = main_table.product_id',
            ['name' => 'value']
        );
        $this->collection->join(
            $this->collection->getTable('eav_attribute'),
            'catalog_product_entity_varchar.attribute_id = eav_attribute.attribute_id',
            []
        );
        $this->collection->join(
            $this->collection->getTable('eav_entity_type'),
            'eav_attribute.entity_type_id = eav_entity_type.entity_type_id',
            []
        );
        $this->collection->addFieldToFilter(
            'eav_entity_type.entity_type_code',
            ['eq' => \Magento\Catalog\Model\Product::ENTITY]
        );
        $this->collection->addFieldToFilter('eav_attribute.attribute_code', ['eq' => 'name']);
        $this->collection->addFieldToFilter('catalog_product_entity_varchar.store_id', ['eq' => 0]);
    }
}
