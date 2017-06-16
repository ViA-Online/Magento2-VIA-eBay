<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use VIAeBay\Connector\Api\Data\OrderInterface;

interface OrderRepositoryInterface
{
    public function save(OrderInterface $page);

    public function getById($id);

    public function getByMagentoId($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(OrderInterface $page);

    public function deleteById($id);
}
