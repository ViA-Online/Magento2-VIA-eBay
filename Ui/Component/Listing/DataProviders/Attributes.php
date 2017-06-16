<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Ui\Component\Listing\DataProviders;

use Magento\Ui\DataProvider\AbstractDataProvider;
use VIAeBay\Connector\Model\ResourceModel\Attribute\CollectionFactory;

class Attributes extends AbstractDataProvider
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
            $this->collection->getTable('eav_attribute'),
            'eav_attribute.attribute_id = main_table.attribute_id',
            ['attribute_code', 'frontend_label']
        );
    }
}
