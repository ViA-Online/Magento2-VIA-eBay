<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use VIAeBay\Connector\Api\Data\ProductVariationInterface;
use VIAeBay\Connector\Api\ProductVariationRepositoryInterface;
use VIAeBay\Connector\Model\ResourceModel\ProductVariation as ProductVariationResource;
use VIAeBay\Connector\Model\ResourceModel\ProductVariation\CollectionFactory;

class ProductVariationRepository implements ProductVariationRepositoryInterface
{
    protected $productVariationResource;
    protected $objectFactory;
    protected $collectionFactory;
    protected $searchResultsFactory;

    public function __construct(
        ProductVariationResource $resource,
        ProductVariationFactory $objectFactory,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    )
    {
        $this->productVariationResource = $resource;
        $this->objectFactory = $objectFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function save(ProductVariationInterface $object)
    {
        try {
            $this->productVariationResource->save($object);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $object;
    }

    public function getById($id)
    {
        $object = $this->objectFactory->create();
        $this->productVariationResource->load($object, $id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }
        return $object;
    }

    public function getByProductIds($parentId, $childId)
    {
        $object = $this->objectFactory->create();
        $this->productVariationResource->loadByProductIds($object, $parentId, $childId);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" "%2" does not exist.', $parentId, $childId));
        }
        return $object;
    }

    public function delete(ProductVariationInterface $object)
    {
        try {
            $this->productVariationResource->delete($object);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    public function deleteByProductIds($parentId, $childId)
    {
        return $this->delete($this->getByProductIds($parentId, $childId));
    }


    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;
    }


}
