<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Api;

use VIAeBay\Connector\Api\Data\ProductVariationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ProductVariationRepositoryInterface 
{
    public function save(ProductVariationInterface $page);

    public function getById($id);

    public function getByProductIds($parent, $child);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(ProductVariationInterface $page);

    public function deleteById($id);
}
