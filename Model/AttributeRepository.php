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
use VIAeBay\Connector\Api\AttributeRepositoryInterface;
use VIAeBay\Connector\Api\Data\AttributeInterface;
use VIAeBay\Connector\Model\ResourceModel\Attribute as VIAAttributeResource;
use VIAeBay\Connector\Model\ResourceModel\Attribute\CollectionFactory as VIAAttributeResourceCollection;

class AttributeRepository implements AttributeRepositoryInterface
{
    /**
     * @var VIAAttributeResource
     */
    protected $attributeResource;
    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;
    /**
     * @var VIAAttributeResourceCollection
     */
    protected $collectionFactory;
    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    public function __construct(
        VIAAttributeResource $attributeResource,
        AttributeFactory $attributeFactory,
        VIAAttributeResourceCollection $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    )
    {
        $this->attributeResource = $attributeResource;
        $this->attributeFactory = $attributeFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function save(AttributeInterface $object)
    {
        try {
            $this->attributeResource->save($object);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $object;
    }

    public function getById($id)
    {
        $object = $this->attributeFactory->create();
        $this->attributeResource->load($object, $id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Object with id "%1" does not exist.', $id));
        }
        return $object;
    }

    public function delete(AttributeInterface $object)
    {
        try {
            $this->attributeResource->delete($object);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
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

    /**
     * @param $attributeCode
     * @return AttributeInterface[]
     */
    public function getByCode($attributeCode)
    {
        $result = $this->attributeResource->loadByAttributeCode($attributeCode);

        $objects = [];
        foreach ($result as $model) {
            $obj = $this->attributeFactory->create();
            $obj->setData($model);
            $objects[] = $obj;
        }
        return $objects;
    }
}
