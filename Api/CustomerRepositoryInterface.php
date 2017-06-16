<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use VIAeBay\Connector\Api\Data\CustomerInterface;

interface CustomerRepositoryInterface
{
    public function save(CustomerInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(CustomerInterface $page);

    public function deleteById($id);
}
