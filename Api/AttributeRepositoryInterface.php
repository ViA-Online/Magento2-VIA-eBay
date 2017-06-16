<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use VIAeBay\Connector\Api\Data\AttributeInterface;


interface AttributeRepositoryInterface
{
    public function save(AttributeInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    /**
     * @param $attributeCode
     * @return AttributeInterface[]
     */
    public function getByCode($attributeCode);

    public function delete(AttributeInterface $page);

    public function deleteById($id);
}
