<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Service;

use GuzzleHttp\Psr7\Uri;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Exception\LocalizedException;
use VIAeBay\Connector\Helper\Configuration;
use VIAeBay\Connector\Helper\OData;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\OData\Changeset;
use VIAeBay\Connector\OData\Client;
use VIAeBay\Connector\OData\Request;

class Category
{
    protected $extensionAttributesFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;
    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;
    /**
     * @var CategoryResource
     */
    protected $categoryResource;
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var OData
     */
    protected $oData;

    /**
     * @var Client
     */
    protected $client;

    protected $viaCategories;
    protected $viaCategoriesMap;
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Category constructor.
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param CategoryFactory $categoryFactory
     * @param CategoryHelper $categoryHelper
     * @param CategoryRepository $categoryRepository
     * @param CategoryResource $categoryResource
     * @param Configuration $configuration
     * @param OData $oData
     * @param Client $client
     * @param Logger $logger
     */
    public function __construct(ExtensionAttributesFactory $extensionAttributesFactory,
                                CategoryFactory $categoryFactory, CategoryHelper $categoryHelper,
                                CategoryRepository $categoryRepository, CategoryResource $categoryResource,
                                Configuration $configuration, OData $oData, Client $client, Logger $logger)
    {
        $this->extensionAttributesFactory = $extensionAttributesFactory;

        $this->categoryFactory = $categoryFactory;
        $this->categoryHelper = $categoryHelper;
        $this->categoryRepository = $categoryRepository;
        $this->categoryResource = $categoryResource;
        $this->configuration = $configuration;
        $this->oData = $oData;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     *
     */
    function sync()
    {
        $seenRemoteCategories = [];
        $seenLocalCategories = [];

        $store = $this->configuration->getStore();
        $magentoCategory = $this->categoryRepository->get($store->getRootCategoryId(), $store->getId());

        $this->syncCategory($magentoCategory, null, $seenRemoteCategories, $seenLocalCategories);

        $changeset = new Changeset();

        foreach ($this->getViaCategories() as $viaCategory) {
            if (empty($viaCategory['ForeignId'])
                || !array_key_exists($viaCategory['ForeignId'], $seenRemoteCategories)
            ) {
                $changeset->addChange($this->oData->deleteObject($viaCategory));
            }
        }

        if (!$changeset->isEmpty()) {
            $this->resetViaCategories();
            $this->client->sendBatch($changeset);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param Uri|null $parentURL
     * @param array $seenRemoteCategories
     * @param array $seenLocalCategories
     * @return array
     * @internal param Changeset $changeset
     */
    function syncCategory(\Magento\Catalog\Model\Category $category, Uri $parentURL = null,
                          array &$seenRemoteCategories, array &$seenLocalCategories)
    {
        $viaCatalog = $this->getViaCategoryById($category->getId());

        $this->logger->addDebug('syncCategory', ['id' => $category->getId(), 'name' => $category->getName()]);

        if ($viaCatalog == null) {
            $viaCatalog = [];
        }

        $viaCatalogDelta = [];

        $this->oData->updateDelta($viaCatalogDelta, $viaCatalog, 'Name', $category->getName());
        $this->oData->updateDelta($viaCatalogDelta, $viaCatalog, 'ForeignId', $category->getId());
        $this->oData->updateDelta($viaCatalogDelta, $viaCatalog, 'IsRootLevel', $category->getLevel() == 1);

        $thisUrl = null;

        if ($viaCatalog == null) {
            $url = new Uri('Catalogs');

            $viaCatalog = $this->client->send(
                new Request('POST', $url, null, $viaCatalogDelta)
            );

            $thisUrl = $this->oData->resolveUri($viaCatalog);
            $this->viaCategories[] = $viaCatalog;
            $this->viaCategoriesMap[$viaCatalog['Id']] = $viaCatalog;
        } elseif (count($viaCatalogDelta) > 0) {
            $thisUrl = $this->oData->resolveUri($viaCatalog);

            $viaCatalog = $this->client->send(
                new Request('MERGE', $thisUrl, null, $viaCatalogDelta)
            );
        }

        $seenRemoteCategories [$category->getId()] = $viaCatalog;
        $seenLocalCategories[$category->getId()] = $category;
        $childrenCategories = $this->categoryResource->getChildrenCategories($category);

        $this->logger->addDebug(__('syncCategory:children:start'), ['parent' => $category->getId(), 'children' => count($childrenCategories)]);
        foreach ($childrenCategories as $childCategory) {
            $this->syncCategory($childCategory, $thisUrl, $seenRemoteCategories, $seenLocalCategories);
        }
        $this->logger->addDebug(__('syncCategory:children:complete'), ['parent' => $category->getId()]);

        $this->syncCategoryRelations($category, $viaCatalog, $seenRemoteCategories, $seenLocalCategories);

        return $viaCatalog;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param array|null $viaCatalog
     * @param array $seenRemoteCategories
     * @param array $seenLocalCategories
     */
    function syncCategoryRelations(\Magento\Catalog\Model\Category $category, $viaCatalog, array &$seenRemoteCategories, array &$seenLocalCategories)
    {
        try {
            //$viaChildCatalog = $this->getViaCategoryById($category->getId());
            $viaChildCatalog = $viaCatalog;

            if ($viaChildCatalog == null) {
                throw new LocalizedException(__('Child category cannot be null', ['id' => $category->getId()]));
            }

            /** @var \Magento\Catalog\Model\Category $parentCategory */
            $parentCategory = array_key_exists($category->getParentId(), $seenLocalCategories) ? $seenLocalCategories [$category->getParentId()] : null;
            $viaParentCatalog = null;

            // Do we have a parent category
            if ($parentCategory == null || $category->getLevel() <= 1) {
                $this->logger->addDebug(__('Root category'), ['category' => $category->getId(), 'catalog' => $viaChildCatalog ['Id']]);
            } else {
                if (array_key_exists($parentCategory->getId(), $seenRemoteCategories)) {
                    $viaParentCatalog = $seenRemoteCategories [$parentCategory->getId()];
                    // Check if connection already exists
                    if ($this->oData->searchCollectionForEntity($viaChildCatalog ['ParentCatalogs'], $parentCategory->getId(), 'ForeignId') != null) {
                    } else {
                        $addRequest = $this->oData->addLink($viaChildCatalog, 'ParentCatalogs', $viaParentCatalog);
                        $this->client->send($addRequest);
                        $this->logger->addDebug(__("Connected child catalog to parent catalog"), ['parent' >= $viaParentCatalog ['Id'], 'child' => $viaChildCatalog ['Id']]);
                    }
                } else {
                    $this->logger->addWarning(__("Mapped category not found"), ['parent' => $parentCategory->getId()]);
                }
            }

            // Remove unused connections
            if (is_array($viaChildCatalog ['ParentCatalogs'])) {
                foreach ($viaChildCatalog ['ParentCatalogs'] as $viaParent) {
                    if (isset ($viaParentCatalog ['Id']) && isset ($viaParent ['Id']) && $viaParentCatalog ['Id'] != $viaParent ['Id']) {
                        $this->client->send($this->oData->deleteLink($viaChildCatalog, 'ParentCatalogs', $viaParent));
                        $this->logger->addDebug(__("Deleted category from parent"), ['parent' => $viaParent ['Id'], 'child' => $viaCatalog ['Id']]);
                    }
                }
            }
        } catch (\Exception $ex) {
            $this->logger->addError($ex->getMessage());
            $this->logger->addDebug($ex->__toString());
        }
    }

    function getViaCategories()
    {
        if ($this->viaCategories === null) {
            $this->viaCategories = $this->client->send(new Request('GET', new Uri('Catalogs?$expand=ParentCatalogs')));
        }

        return $this->viaCategories;
    }

    function getViaCategoryById($id)
    {
        if ($id == null) {
            return null;
        }

        if ($this->viaCategoriesMap === null) {
            $this->viaCategoriesMap = [];

            foreach ($this->getViaCategories() as $viaCategory) {
                $this->viaCategoriesMap[$viaCategory['ForeignId']] = $viaCategory;
            }
        }

        if (array_key_exists($id, $this->viaCategoriesMap)) {
            return $this->viaCategoriesMap[$id];
        } else {
            return null;
        }
    }

    function resetViaCategories()
    {
        $this->viaCategories = null;
        $this->viaCategoriesMap = null;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Api\Data\CategoryExtensionInterface
     */
    public function getExtensionAttributes(\Magento\Catalog\Model\Category $category)
    {
        $extensionAttributes = $category->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create('Magento\Catalog\Api\Data\CategoryInterface');
            $category->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}
