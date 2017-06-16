<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use VIAeBay\Connector\Api\Data\CategoryInterface;

interface CategoryRepositoryInterface
{
    public function save(CategoryInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(CategoryInterface $page);

    public function deleteById($id);
}
