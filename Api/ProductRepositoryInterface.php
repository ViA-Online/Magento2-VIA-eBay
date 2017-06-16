<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use VIAeBay\Connector\Api\Data\ProductInterface;

interface ProductRepositoryInterface
{
    public function save(ProductInterface $page);

    public function getById($id);

    public function getByProductId($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(ProductInterface $page);

    public function deleteById($id);
}
