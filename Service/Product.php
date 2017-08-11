<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Service;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Uri;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image as HelperProductImage;
use Magento\Catalog\Helper\Product as HelperProduct;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockRegistryInterface\Proxy as StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\FilterBuilder\Proxy as FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder\Proxy as SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use VIAeBay\Connector\Helper\Attribute as VIAAttributeHelper;
use VIAeBay\Connector\Helper\Configuration;
use VIAeBay\Connector\Helper\OData;
use VIAeBay\Connector\Helper\Product as VIAProductHelper;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Model\AttributeRepository as VIAAttributeRepository;
use VIAeBay\Connector\Model\ProductFactory as VIAProductFactory;
use VIAeBay\Connector\Model\ProductRepository as VIAProductRepository;
use VIAeBay\Connector\Model\ProductVariationFactory as VIAProductVariationFactory;
use VIAeBay\Connector\Model\ProductVariationRepository as VIAProductVariationRepository;
use VIAeBay\Connector\Model\ResourceModel\Backlog\Product as BacklogProductResource;
use VIAeBay\Connector\Model\ResourceModel\Backlog\Product\Collection as BacklogProductResourceCollection;
use VIAeBay\Connector\OData\Changeset;
use VIAeBay\Connector\OData\Client;
use VIAeBay\Connector\OData\Request;
use VIAeBay\Connector\Service\Backlog as VIAProductBacklogService;


/**
 * Class Product. Uses to sync products to VIA-eBay.
 * @package VIAeBay\Connector\Service
 */
class Product
{
    const VIAEBAY_EXPORT_ATTRIBUTE = 'viaebay_export';
    const VIAEBAY_RECURSION_PREVENT_ATTRIBUTE = 'viaebay_recursion_prevent';
    const VIAEBAY_AUTO_ACCEPT_PRICE_ATTRIBUTE = 'viaebay_auto_accept_price';
    const VIAEBAY_AUTO_DECLINE_PRICE_ATTRIBUTE = 'viaebay_auto_decline_price';
    const VIAEBAY_SHIPPING_PROFILE_ID_ATTRIBUTE = 'viaebay_shipping_profile_id';

    /**
     * Type id of listing images.
     */
    const listingImageType = 1;

    /**
     * Type id of gallery images.
     */
    const galleryImageType = 2;

    /**
     * Expands used to load products.
     */
    const productExpands = [
        'CarParts',
        'Catalogs',
        'ChildProducts',
        'DiscountOffers',
        'OptionalProductAttributes',
        'ProductImages',
        'ProductSpecifics',
        'ProductVariations/ProductVariationSpecifics',
        'ProductVariations/ProductVariationPictures'
    ];

    /**
     * @var ProductFactory
     */
    protected $productFactory;
    /**
     * @var HelperProduct
     */
    protected $productHelper;
    /**
     * @var HelperProductImage
     */
    protected $productImageHelper;
    /**
     * @var ProductRepository
     */
    protected $productRepository;
    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var Visibility
     */
    protected $visibility;
    /**
     * @var Configuration
     */
    protected $data;
    /**
     * @var OData
     */
    protected $oData;
    /**
     * @var VIAAttributeHelper
     */
    protected $viaAttributeHelper;
    /**
     * @var VIAProductHelper
     */
    protected $viaProductHelper;
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var Category
     */
    protected $categoryService;

    /**
     * @var BacklogProductResourceCollection
     */
    protected $productBacklogResourceCollection;

    /**
     * @var BacklogProductResource
     */
    protected $productBacklogResource;

    /**
     * @var array
     */
    protected $viaProducts;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var VIAAttributeRepository
     */
    protected $viaAttributeRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var VIAProductBacklogService
     */
    protected $viaProductBacklogService;

    /**
     * @var VIAProductRepository
     */
    protected $viaProductRepository;

    /**
     * @var VIAProductFactory
     */
    protected $viaProductFactory;

    /**
     * @var VIAProductVariationFactory
     */
    protected $viaProductVariationFactory;

    /**
     * @var VIAProductVariationRepository
     */
    protected $viaProductVariationRepository;

    /**
     * Category constructor.
     * @param ProductFactory $productFactory
     * @param ProductRepository $productRepository
     * @param ProductResource $productResource
     * @param HelperProduct $productHelper
     * @param HelperProductImage $productImageHelper
     * @param Visibility $visibility
     * @param CategoryRepository $categoryRepository
     * @param Category $category
     * @param BacklogProductResource $productBacklogResource
     * @param StockRegistryInterface $stockRegistry
     * @param BacklogProductResourceCollection $productBacklogResourceCollection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param Configuration $data
     * @param OData $oData
     * @param VIAAttributeRepository $attributeRepository
     * @param VIAAttributeHelper $viaAttributeHelper
     * @param VIAProductHelper $viaProductHelper
     * @param Backlog $viaProductBacklogService
     * @param VIAProductFactory $viaProductFactory
     * @param VIAProductRepository $viaProductRepository
     * @param VIAProductVariationFactory $viaProductVariationFactory
     * @param VIAProductVariationRepository $viaProductVariationRepository
     * @param Client $client
     * @param Logger $logger
     * @internal param \Magento\Catalog\Helper\Category $categoryHelper
     * @internal param \Magento\Catalog\Model\ResourceModel\Category $categoryResource
     */
    public function __construct(ProductFactory $productFactory, ProductRepository $productRepository,
                                ProductResource $productResource, HelperProduct $productHelper,
                                HelperProductImage $productImageHelper, Visibility $visibility,
                                CategoryRepository $categoryRepository, Category $category,
                                BacklogProductResource $productBacklogResource, StockRegistryInterface $stockRegistry,
                                BacklogProductResourceCollection $productBacklogResourceCollection,
                                SearchCriteriaBuilder $searchCriteriaBuilder, FilterBuilder $filterBuilder,
                                Configuration $data, OData $oData,
                                VIAAttributeRepository $attributeRepository,
                                VIAAttributeHelper $viaAttributeHelper,
                                VIAProductHelper $viaProductHelper,
                                VIAProductBacklogService $viaProductBacklogService,
                                VIAProductFactory $viaProductFactory,
                                VIAProductRepository $viaProductRepository,
                                VIAProductVariationFactory $viaProductVariationFactory,
                                VIAProductVariationRepository $viaProductVariationRepository,
                                Client $client,
                                Logger $logger)
    {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->productResource = $productResource;
        $this->productHelper = $productHelper;
        $this->productImageHelper = $productImageHelper;
        $this->stockRegistry = $stockRegistry;
        $this->visibility = $visibility;
        $this->productBacklogResource = $productBacklogResource;
        $this->productBacklogResourceCollection = $productBacklogResourceCollection;
        $this->categoryRepository = $categoryRepository;
        $this->categoryService = $category;

        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->data = $data;
        $this->oData = $oData;
        $this->viaAttributeHelper = $viaAttributeHelper;
        $this->viaProductHelper = $viaProductHelper;
        $this->client = $client;
        $this->logger = $logger;
        $this->viaAttributeRepository = $attributeRepository;
        $this->viaProductBacklogService = $viaProductBacklogService;
        $this->viaProductRepository = $viaProductRepository;
        $this->viaProductFactory = $viaProductFactory;
        $this->viaProductVariationFactory = $viaProductVariationFactory;
        $this->viaProductVariationRepository = $viaProductVariationRepository;
    }

    /**
     * Run product export.
     */
    public function exportProducts()
    {
        $seenMagentoProducts = [];

        $this->viaProductBacklogService->setDisabled(true);

        //$transaction = $this->productBacklogResource->beginTransaction();

        try {
            $backlogs = $this->productBacklogResourceCollection;
            $backlogs->load();

            foreach ($backlogs as $productBacklog) {
                $productId = $productBacklog->getProductId();

                try {
                    if (!isset ($seenMagentoProducts [$productId])) {
                        $seenMagentoProducts [$productId] = true;

                        $this->exportProductById($productId);
                    } else {
                        //$this->logger->addWarning('Product already synced', ['productId' => $productId]);
                    }

                    $this->productBacklogResource->delete($productBacklog);
                } catch (\Exception $ex) {
                    $this->logger->addError(__('Error syncing product'), ['productId' => $productId]);
                    $this->logger->addError($ex->getMessage());
                    $this->logger->addDebug($ex->__toString());
                }
            }
        } catch (\Exception $e) {
            //$transaction->rollBack();
            throw $e;
        }
        //$transaction->commit();

        $this->viaProductBacklogService->setDisabled(false);
    }

    /**
     * Export single product by id.
     *
     * @param int $productId
     * @return MagentoProduct
     */
    public function exportProductById(int $productId)
    {
        $product = $this->loadProductById($productId);
        if ($product) {
            $this->exportProduct($product);
        }
        return $product;
    }

    /**
     * Load single product by id using VIA-eBay store.
     *
     * @param int $productId
     * @return MagentoProduct
     * @throws LocalizedException
     */
    protected function loadProductById(int $productId)
    {
        $viaEbayStoreId = $this->data->getStoreId();

        if ($viaEbayStoreId <= 0) {
            throw new LocalizedException(__('StoreId is not set'));
        }

        $product = $this->productRepository->getById($productId, false, $viaEbayStoreId);

        if ($product instanceof MagentoProduct) {
            return $product;
        } else {
            throw new LocalizedException(__('Product has invalid class %1', get_class($product)));
        }
    }

    /**
     * Export product.
     *
     * @param MagentoProduct $product
     * @return void
     */
    public function exportProduct(MagentoProduct $product)
    {
        if ($product == null || !$product->getId() || $product->getData(self::VIAEBAY_RECURSION_PREVENT_ATTRIBUTE) === true || !($product instanceof MagentoProduct)) {
            // Prevent recursion
            return;
        }

        $product->setData(self::VIAEBAY_RECURSION_PREVENT_ATTRIBUTE, true);

        $viaExportAttribute = $product->getCustomAttribute(self::VIAEBAY_EXPORT_ATTRIBUTE);

        if ($product->isDisabled() || $viaExportAttribute == null || !$viaExportAttribute->getValue()
            || !in_array($product->getVisibility(), [Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH])
        ) {
            // Not enabled and exported -> ignore
            try {
                $this->deleteProduct($product);
            } catch (\Exception $e) {
                //$this->log->log('Could not delete already deleted product m' . $product->getId(), Zend_Log::ERR);
                //$result->error++;
            }
            return;
        }

        try {
            $productExtension = $this->viaProductRepository->getByProductId($product->getId());
        } catch (NoSuchEntityException $e) {
            $productExtension = $this->viaProductFactory->create();
            $productExtension->setProductId($product->getId());
        }
        $product->getExtensionAttributes()->setVIAeBayConnector($productExtension);

        $viaProduct = $this->oData->searchCollectionByKeyAndValue($this->getViaProducts(), ['ForeignId' => $product->getId()]);
        $viaProductDelta = [];

        $productType = null;

        switch ($product->getTypeId()) {
            case Configurable::TYPE_CODE:
                $productType = 1;
                break;
            case Grouped::TYPE_CODE:
                $productType = 2;
                break;
            default :
                $productType = 0;
                break;
        }

        $qtyStock = $this->viaProductHelper->getQtyStock($product);
        $mappedValues = $this->viaProductHelper->getMappedAttributeValues($product);

        $autoAcceptPriceAttribute = $product->getCustomAttribute(self::VIAEBAY_AUTO_ACCEPT_PRICE_ATTRIBUTE);
        if ($autoAcceptPriceAttribute != null) {
            $autoAcceptPrice = $autoAcceptPriceAttribute->getValue();
        } else {
            $autoAcceptPrice = null;
        }

        $autoDeclinePriceAttribute = $product->getCustomAttribute(self::VIAEBAY_AUTO_DECLINE_PRICE_ATTRIBUTE);
        if ($autoDeclinePriceAttribute != null) {
            $autoDeclinePrice = $autoDeclinePriceAttribute->getValue();
        } else {
            $autoDeclinePrice = null;
        }

        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'ExternalProductId', $product->getId());
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'StockAmount', intval($qtyStock));
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'Title', $mappedValues ['Title']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'Price', ( string )( float )$mappedValues ['Price']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'Description', $mappedValues ['Description']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'ShortDescription', $mappedValues ['ShortDescription']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'Ean', $mappedValues ['Ean']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'Isbn', $mappedValues ['Isbn']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'Upc', $mappedValues ['Upc']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'Mpn', $mappedValues ['Mpn']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'Brand', $mappedValues ['Brand']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'UnitQuantity', $mappedValues ['UnitQuantity']);
        $this->oData->updateDelta($viaProductDelta, $viaProduct, 'UnitType', $mappedValues ['UnitType']);

        if ($mappedValues ['Price'] > $autoAcceptPrice && $autoAcceptPrice > $autoDeclinePrice && $autoAcceptPrice != 0 && $autoDeclinePrice != 0) {
            $this->oData->updateDelta($viaProductDelta, $viaProduct, 'BestOfferAutoAcceptPrice', ( string )$autoAcceptPrice);
            $this->oData->updateDelta($viaProductDelta, $viaProduct, 'BestOfferMinimumPrice', ( string )$autoDeclinePrice);
        } else {
            $this->oData->updateDelta($viaProductDelta, $viaProduct, 'BestOfferAutoAcceptPrice', NULL);
            $this->oData->updateDelta($viaProductDelta, $viaProduct, 'BestOfferMinimumPrice', NULL);
        }

        if ($viaProduct == null) {
            $viaProductDelta ['ProductType'] = $productType;
            $viaProductDelta ['ForeignId'] = $product->getId();

            $viaProduct = $this->client->send($this->oData->saveObject('Products', $viaProductDelta));

            $this->logger->addDebug(__("Added product"), $viaProductDelta);
        } elseif (count($viaProductDelta)) {
            $this->client->send($this->oData->updateObject($viaProduct, $viaProductDelta));
            $this->logger->addDebug(__("Updated product"), $viaProductDelta);
        } else {
            //TODO:
        }

        $productExtension->setVIAeBayId($viaProduct ['Id']);
        $this->viaProductRepository->save($productExtension);

        $exportedProducts = [];
        if ($product->getTypeId() != Configurable::TYPE_CODE) {
            $exportedProducts [] = [
                'product' => $product,
                'variation' => null,
                'via_product' => $viaProduct,
                'via_variation' => null,
                'values' => $mappedValues
            ];
        }

        $this->exportProductSpecifics($product, $viaProduct);
        $this->exportOptionalProductAttributes($product, $viaProduct);
        $this->exportProductVariations($product, $viaProduct, $exportedProducts);
        $this->exportDiscountOffer($viaProduct, $exportedProducts);
        $this->exportProductCategories($product, $viaProduct);
        $this->exportProductImages($product, $viaProduct);

        $shippingProfileIdAttribute = $product->getCustomAttribute(self::VIAEBAY_SHIPPING_PROFILE_ID_ATTRIBUTE);
        $shippingProfileId = $shippingProfileIdAttribute != null ? $shippingProfileIdAttribute->getValue() : null;

        if ($shippingProfileId) {
            $this->client->send(
                $this->oData->call("SetProductProfile", [
                    'productId' => $viaProduct ['Id'] . 'L',
                    'type' => 3,
                    'profileId' => $shippingProfileId . 'L'
                ])
            );
        }
    }

    /**
     * Delete product.
     *
     * @param MagentoProduct $product
     * @param Changeset|null $changeset
     */
    public function deleteProduct(MagentoProduct $product, $changeset = null)
    {
        try {
            $productExtension = $this->viaProductRepository->getByProductId($product->getId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        if (empty($productExtension->getVIAeBayId())) {
            return;
        }

        $newChangeset = false;
        if ($changeset == null || !($changeset instanceof Changeset)) {
            $changeset = new Changeset();
            $newChangeset = true;
        }

        $this->deleteProductAsVariation($product, $changeset);

        $viaebayProduct = $this->getViaProduct($productExtension->getVIAeBayId());

        if ($viaebayProduct) {
            if (is_array($viaebayProduct ['ProductSpecifics'])) {
                foreach ($viaebayProduct ['ProductSpecifics'] as $viaProductSpecific) {
                    $changeset->addChange($this->oData->deleteObject($viaProductSpecific));
                }
            }

            if (is_array($viaebayProduct ['ProductSpecifics'])) {
                foreach ($viaebayProduct ['ProductSpecifics'] as $viaProductSpecific) {
                    $changeset->addChange($this->oData->deleteObject($viaProductSpecific));
                }
            }

            if (is_array($viaebayProduct ['OptionalProductAttributes'])) {
                foreach ($viaebayProduct ['OptionalProductAttributes'] as $viaOptionalProductAttributes) {
                    $changeset->addChange($this->oData->deleteObject($viaOptionalProductAttributes));
                }
            }

            if (is_array($viaebayProduct ['DiscountOffers'])) {
                foreach ($viaebayProduct ['DiscountOffers'] as $discountOffer) {
                    $changeset->addChange($this->oData->deleteObject($discountOffer));
                }
            }

            if (is_array($viaebayProduct ['ProductImages'])) {
                foreach ($viaebayProduct ['ProductImages'] as $productImage) {
                    if (!empty($productImage)) {
                        $changeset->addChange($this->oData->deleteObject($productImage));
                    }
                }
            }

            if (is_array($viaebayProduct ['ProductVariations'])) {
                foreach ($viaebayProduct ['ProductVariations'] as $productVariation) {
                    if (!empty($productVariation)) {
                        $this->deleteProductVariations($productVariation, $changeset);
                    }
                }
            }

            $changeset->addChange($this->oData->deleteObject($viaebayProduct));
        }

        try {
            if ($newChangeset && !$changeset->isEmpty()) {
                $this->client->sendBatch($changeset);
            }
        } catch (ClientException $e) {
            $this->logger->addError($e->getMessage());
            $this->logger->addDebug($e->__toString());
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
            $this->logger->addDebug($e->__toString());
        }

        // Product is new we need to save the id.
        $productExtension->setVIAeBayId(null);
        $this->viaProductRepository->save($productExtension);
    }

    /**
     * Delete variations by product.
     *
     * @param MagentoProduct $product
     * @param Changeset|null $changeset
     */
    public function deleteProductAsVariation(MagentoProduct $product, $changeset = null)
    {
        try {
            $productExtension = $this->viaProductRepository->getByProductId($product->getId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        if (empty($productExtension->getVIAeBayId())) {
            return;
        }

        $newChangeset = false;
        if ($changeset == null || !($changeset instanceof Changeset)) {
            $changeset = new Changeset();
            $newChangeset = true;
        }

        $uri = $this->oData->uriAppendParameters(new Uri('ProductVariations'), ['$filter' => 'ProductId eq ' . $productExtension->getVIAeBayId()]);
        $variations = $this->client->send(new Request('GET', $uri));

        foreach ($variations as $viaProductVariation) {
            try {
                // Set StockAmount to 0 as Variations cannot be deleted on ebay.
                $changeset->addChange($this->oData->updateObject($viaProductVariation, ['StockAmount' => 0]));

                $simpleProduct = $this->loadProductById($viaProductVariation ['ForeignId']);

                if ($simpleProduct) {
                    try {
                        $this->viaProductVariationRepository->deleteByProductIds($product->getId(), $simpleProduct->getId());
                    } catch (NoSuchEntityException $e) {
                        $this->logger->addDebug('Could not delete product');
                    }
                }
            } catch (\Exception $e) {
                $this->logger->addError($e->getMessage());
                $this->logger->addDebug($e->__toString());
            }
        }

        if ($newChangeset && !$changeset->isEmpty()) {
            $this->client->sendBatch($changeset);
        }
    }

    /**
     * @param array $viaProductVariation
     * @param Changeset|null $changeset
     */
    protected function deleteProductVariations(array $viaProductVariation, $changeset)
    {
        $newChangeset = false;
        if ($changeset == null || !($changeset instanceof Changeset)) {
            $changeset = new Changeset();
            $newChangeset = true;
        }

        if (!empty($viaProductVariation)) {
            $changeset->addChange($this->oData->updateObject($viaProductVariation, ['StockAmount' => 0]));
        }

        //$changeset->addChange($this->oData->deleteObject($viaProductVariation));

        if (is_array($viaProductVariation ['ProductVariationSpecifics'])) {
            foreach ($viaProductVariation ['ProductVariationSpecifics'] as $productVariationSpecific) {
                if (!empty($productVariationSpecific)) {
                    $changeset->addChange($this->oData->deleteObject($productVariationSpecific));
                }
            }
        }

        if (is_array($viaProductVariation ['ProductVariationPictures'])) {
            foreach ($viaProductVariation ['ProductVariationPictures'] as $productVariationPicture) {
                if (!empty($productVariationPicture)) {
                    $changeset->addChange($this->oData->deleteObject($productVariationPicture));
                }
            }
        }

        if ($newChangeset && !$changeset->isEmpty()) {
            $this->client->sendBatch($changeset);
        }
    }

    /**
     * Delete given $viaEntites if they are not listed in $seenEntityIds
     *
     * @param array $viaEntities all entities known to via
     * @param array $seenEntityIds entities already seen
     * @param Callable|null $deleteCallable callable used to delete entity.
     * @return int number of deleted entities
     */
    protected function deleteEntityIfIdHasNotBeenSeen(array $viaEntities, $seenEntityIds, $deleteCallable = null)
    {
        $deleted = 0;
        foreach ($viaEntities as $entity) {
            try {
                if (!isset ($entity ['Id'])) {
                    $this->logger->addDebug(__("Entity without Id. Probably new."));
                    continue;
                } else if (isset ($seenEntityIds [$entity ['Id']])) {
                    $this->logger->addDebug(__("Don't delete seen entity "), ['id' => $entity ['Id']]);
                    continue;
                }

                $this->logger->addDebug(__("Deleting entity"), ['metadata' => $entity ['__metadata']]);
                if ($deleteCallable == null) {
                    $this->client->send($this->oData->deleteObject($entity));
                } else {
                    call_user_func($deleteCallable, [$entity]);
                }
                $deleted++;
            } catch (\Exception $ex) {
                $this->logger->addError($ex->getMessage());
                $this->logger->addDebug($ex->__toString());
            }
        }
        return $deleted;
    }

    /**
     * Export product attributes.
     *
     * @param MagentoProduct $product
     * @param array $viaProduct
     * @throws \Exception
     */
    private function exportProductSpecifics(MagentoProduct $product, $viaProduct)
    {
        $seenRemoteAttributes = [];

        foreach ($product->getAttributes() as $attribute) {
            //$attributeExtensions = $this->_viaAttributeHelper->getExtensionAttributes($attribute);

            $types = $this->viaAttributeRepository->getByCode($attribute->getAttributeCode());

            if (empty($types)) {
                continue;
            }

            foreach ($types as $type) {
                if ($type->getType() != 'CustomAttribute') {
                    continue;
                }

                /*
                $productValue = $product->getData($attribute->getAttributeCode());

                if ($attribute->isValueEmpty($productValue) || empty ($productValue)) {
                    continue;
                }
                */

                $frontendValue = $attribute->getFrontend()->getValue($product);

                if (empty ($frontendValue) || !is_scalar($frontendValue)) {
                    continue;
                }

                //$storeLabel = $attribute->getDefaultFrontendLabel();
                $storeLabel = $attribute->getStoreLabel($this->data->getStoreId());

                //TODO: Get store label by store...

                if (strlen($storeLabel) <= 0) {
                    continue;
                }

                $viaProductSpecific = $this->oData->searchCollectionForEntity($viaProduct ['ProductSpecifics'], $storeLabel, 'Name');
                $viaProductSpecificsDelta = [];

                $this->oData->updateDelta($viaProductSpecificsDelta, $viaProductSpecific, 'Value', $frontendValue);

                if ($viaProductSpecific == null) {
                    $viaProductSpecificsDelta ['ProductId'] = $viaProduct ['Id'];
                    $viaProductSpecificsDelta ['Name'] = $storeLabel;
                    $viaProductSpecific = $this->client->send($this->oData->saveObject('ProductSpecifics', $viaProductSpecificsDelta));
                    $this->logger->addDebug(__("Added product specifics"), $viaProductSpecificsDelta);
                } elseif (count($viaProductSpecificsDelta)) {
                    $this->client->send($this->oData->updateObject($viaProductSpecific, $viaProductSpecificsDelta));
                    $this->logger->addDebug(__("Updated product specifics"), $viaProductSpecificsDelta);
                } else {
                    // Nothing to do ;-)
                }

                $seenRemoteAttributes [$viaProductSpecific ['Id']] = $viaProductSpecific;
            }
        }

        if (is_array($viaProduct ['ProductSpecifics'])) {
            $this->deleteEntityIfIdHasNotBeenSeen($viaProduct ['ProductSpecifics'], $seenRemoteAttributes);
        }
    }

    /**
     * Export optional product attributes.
     *
     * @param MagentoProduct $product
     * @param array $viaProduct
     * @throws \Exception
     */
    private function exportOptionalProductAttributes(MagentoProduct $product, $viaProduct)
    {
        $seenRemoteAttributes = array();

        foreach ($product->getAttributes() as $attribute) {
            //$attributeExtensions = $this->_viaAttributeHelper->getExtensionAttributes($attribute);

            $types = $this->viaAttributeRepository->getByCode($attribute->getAttributeCode());

            foreach ($types as $type) {
                if ($type->getType() != 'OptionalAttribute') {
                    continue;
                }

                $frontendValue = $attribute->getFrontend()->getValue($product);

                if (empty ($frontendValue) || !is_scalar($frontendValue)) {
                    continue;
                }

                $storeLabel = $attribute->getStoreLabel($this->data->getStoreId());

                if (strlen($storeLabel) <= 0) {
                    continue;
                }

                $viaOptionalAttribute = $this->oData->searchCollectionForEntity($viaProduct ['OptionalProductAttributes'], $storeLabel, 'Name');

                $viaOptionalAttributeDelta = array();

                $this->oData->updateDelta($viaOptionalAttributeDelta, $viaOptionalAttribute, 'Value', $frontendValue);

                if ($viaOptionalAttribute == null) {
                    $viaOptionalAttributeDelta ['ProductId'] = $viaProduct ['Id'];
                    $viaOptionalAttributeDelta ['Name'] = $storeLabel;
                    $viaOptionalAttribute = $this->client->send($this->oData->saveObject('OptionalProductAttributes', $viaOptionalAttributeDelta));
                    $this->logger->addDebug(__("Added optional attribute"), $viaOptionalAttributeDelta);
                } elseif (count($viaOptionalAttributeDelta)) {
                    $this->client->send($this->oData->updateObject($viaOptionalAttribute, $viaOptionalAttributeDelta));
                    $this->logger->addDebug(__("Updated optional attribute"), $viaOptionalAttributeDelta);
                } else {
                    // Nothing to do ;-)
                }

                $seenRemoteAttributes [$viaOptionalAttribute ['Id']] = $viaOptionalAttribute;
            }
        }

        if (is_array($viaProduct ['OptionalProductAttributes'])) {
            $this->deleteEntityIfIdHasNotBeenSeen($viaProduct ['OptionalProductAttributes'], $seenRemoteAttributes);
        }
    }

    /**
     * Export product variations.
     *
     * @param ProductInterface|MagentoProduct $product
     * @param array $viaProduct
     * @param $exportedProducts
     * @return void
     */
    private function exportProductVariations(MagentoProduct $product, $viaProduct, &$exportedProducts)
    {
        $typeInstance = $product->getTypeInstance();

        if (!($typeInstance instanceof Configurable)) {
            return;
        }

        $productAttributeOptions = $typeInstance->getConfigurableAttributeCollection($product)->orderByPosition();

        $this->logger->addDebug(__("Variation attribute storeId"), ['storeId' => $productAttributeOptions->getStoreId()]);

        $simpleProducts = $typeInstance->getUsedProductCollection($product)->addAttributeToSelect('*')->addFilterByRequiredOptions();

        $seenRemoteVariations = [];

        foreach ($simpleProducts as $simpleProduct) {
            /* @var $simpleProduct MagentoProduct */

            if ($simpleProduct->isDisabled()) {
                continue;
            }

            $simpleProduct = $this->loadProductById($simpleProduct->getId());
            if (!$simpleProduct) {
                $this->logger->addDebug('Could not load product', ['id' => $simpleProduct->getId()]);
                continue;
            }

            $mappedValues = $this->viaProductHelper->getMappedAttributeValues($simpleProduct, $product);

            $viaProductVariation = $this->oData->searchCollectionForEntity($viaProduct ['ProductVariations'], $simpleProduct->getId(), 'ForeignId');
            if ($viaProductVariation == null) {
                // Fix missing ForeignId from API.
                $viaProductVariation = $this->oData->searchCollectionForEntity($viaProduct ['ProductVariations'], $simpleProduct->getSku(), 'Sku');
            }

            try {
                $simpleProductExtension = $this->viaProductVariationRepository->getByProductIds($product->getId(), $simpleProduct->getId());
            } catch (NoSuchEntityException $e) {
                $simpleProductExtension = $this->viaProductVariationFactory->create();
                $simpleProductExtension->setProductId($simpleProduct->getId());
                $simpleProductExtension->setVIAeBayProductId($product->getExtensionAttributes()->getVIAeBayConnector()->getId());
            }

            $qtyStock = $this->viaProductHelper->getQtyStock($simpleProduct);

            $viaProductVariationDelta = array();

            $this->oData->updateDelta($viaProductVariationDelta, $viaProductVariation, 'ExternalProductId', $simpleProduct->getId());
            $this->oData->updateDelta($viaProductVariationDelta, $viaProductVariation, 'Ean', $mappedValues ['Ean'], false);
            $this->oData->updateDelta($viaProductVariationDelta, $viaProductVariation, 'Isbn', $mappedValues ['Isbn'], false);
            $this->oData->updateDelta($viaProductVariationDelta, $viaProductVariation, 'Upc', $mappedValues ['Upc'], false);
            $this->oData->updateDelta($viaProductVariationDelta, $viaProductVariation, 'Mpn', $mappedValues ['Mpn'], false);
            $this->oData->updateDelta($viaProductVariationDelta, $viaProductVariation, 'Price', ( string )( float )$mappedValues ['Price']);
            $this->oData->updateDelta($viaProductVariationDelta, $viaProductVariation, 'StockAmount', intval($qtyStock > 0 ? $qtyStock : 0));
            $this->oData->updateDelta($viaProductVariationDelta, $viaProductVariation, 'ForeignId', $simpleProduct->getId());

            if ($viaProductVariation == null) {
                $viaProductVariationDelta ['ProductId'] = $viaProduct ['Id'];
                $viaProductVariationDelta ['Sku'] = $simpleProduct->getSku();

                $viaProductVariation = $this->client->send($this->oData->saveObject('ProductVariations', $viaProductVariationDelta));

                $this->logger->addDebug(__("Added product variation"), $viaProductVariationDelta);
            } elseif (count($viaProductVariationDelta)) {
                $this->client->send($this->oData->updateObject($viaProductVariation, $viaProductVariationDelta));
                $this->logger->addDebug(__("Updated product variation"), $viaProductVariationDelta);
            } else {
                //Nothing to do
            }

            // Product is new we need to save the id.

            $simpleProductExtension->setVIAeBayId($viaProductVariation ['Id']);
            $this->viaProductVariationRepository->save($simpleProductExtension);

            $seenRemoteVariations [$viaProductVariation ['Id']] = $simpleProduct->getId();

            $this->exportProductVariationSpecifics($simpleProduct, $viaProductVariation, $productAttributeOptions);
            $this->exportProductVariationPictures($simpleProduct, $viaProductVariation);

            $exportedProducts [] = array(
                'product' => $product,
                'variation' => $simpleProduct,
                'via_product' => $viaProduct,
                'via_variation' => $viaProductVariation,
                'values' => $mappedValues
            );
        }

        if (is_array($viaProduct ['ProductVariations'])) {
            foreach ($viaProduct ['ProductVariations'] as $viaProductVariation) {
                if (isset ($viaProductVariation ['Id']) && (!isset ($seenRemoteVariations [$viaProductVariation ['Id']]))) {
                    // Variations cannot be deleted from ebay after a sale. We set StockAmount to 0 instead of deleting.
                    if ($viaProductVariation ['StockAmount'] > 0) {
                        $this->client->send($this->oData->updateObject($viaProductVariation, [
                            'StockAmount' => 0
                        ]));
                    }

                    // Product is new we need to save the id.

                    $this->viaProductVariationRepository->deleteByProductIds($product->getId(), $viaProductVariation['ForeignId']);
                }
            }
        }
    }

    /**
     * Export product variation specifics.
     *
     * @param MagentoProduct $product
     * @param array $viaProductVariation
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection $configurableAttributes
     */
    private function exportProductVariationSpecifics(MagentoProduct $product, $viaProductVariation, \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute\Collection $configurableAttributes)
    {
        $seenRemoteVariationsSpecifics = array();

        foreach ($configurableAttributes as $configurableAttribute) {
            /* @var $configurableAttribute \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute */
            $productAttribute = $configurableAttribute->getProductAttribute();
            $attributeCode = $productAttribute->getAttributeCode();

            $this->logger->addDebug(__("Using default attribute label"));
            $name = $productAttribute->getStoreLabel($this->data->getStoreId());
            $this->logger->addDebug(__("Attribute label", ['name' => $name]));

            $value = $productAttribute->getFrontend()->getValue($product);

            $viaProductVariationSpecific = $this->oData->searchCollectionForEntity($viaProductVariation ['ProductVariationSpecifics'], $name, 'Name');

            $viaProductVariationSpecificDelta = array();

            $this->oData->updateDelta($viaProductVariationSpecificDelta, $viaProductVariationSpecific, 'Value', $value);

            if ($viaProductVariationSpecific == null) {
                $viaProductVariationSpecificDelta ['ProductVariationId'] = $viaProductVariation ['Id'];
                $viaProductVariationSpecificDelta ['Name'] = $name;
                $viaProductVariationSpecific = $this->client->send($this->oData->saveObject('ProductVariationSpecifics', $viaProductVariationSpecificDelta));
                $this->logger->addDebug(__("Added product variation specific"), $viaProductVariationSpecificDelta);
            } elseif (count($viaProductVariationSpecificDelta)) {
                $viaProductVariationSpecific = $this->client->send($this->oData->updateObject($viaProductVariationSpecific, $viaProductVariationSpecificDelta));
                $this->logger->addDebug(__("Updated product variation specific"), $viaProductVariationSpecificDelta);
            } else {
                // Nothing to do
            }

            $seenRemoteVariationsSpecifics [$viaProductVariationSpecific ['Id']] = $viaProductVariationSpecific;
        }

        if (is_array($viaProductVariation ['ProductVariationSpecifics'])) {
            $this->deleteEntityIfIdHasNotBeenSeen($viaProductVariation ['ProductVariationSpecifics'], $seenRemoteVariationsSpecifics);
        }
    }

    /**
     * Export product images.
     *
     * @param MagentoProduct $product
     * @param array $viaProductVariation
     */
    private function exportProductVariationPictures(MagentoProduct $product, array $viaProductVariation)
    {
        $seenRemoteImages = array();

        $imagePos = 1;
        $images = $product->getMediaGalleryEntries();

        $imageUrls = array();

        if ($images != null) {
            foreach ($images as $image) {
                if ($image->isDisabled()) {
                    continue;
                }

                if ($imagePos >= 12) {
                    $this->logger->addWarning(__('More than 10 images for product'), ['Id' => $viaProductVariation ['Id']]);
                    break;
                }

                $url = $this->productImageHelper->init($product, $image->getId())->setImageFile($image->getFile())->getUrl();

                if (in_array('base', $image->getTypes())) {
                    $imageUrls[0] = $url;
                    continue;
                }

                $imageUrls[$imagePos++] = $url;
            }

            foreach ($imageUrls as $imagePos => $imageUrl) {
                $viaProductImage = $this->exportProductVariationPicture($viaProductVariation, $imageUrl, $imagePos);

                if (is_array($viaProductImage)) {
                    $seenRemoteImages [$viaProductImage ['Id']] = true;
                }
            }
        }

        if (is_array($viaProductVariation ['ProductVariationPictures'])) {
            foreach ($viaProductVariation ['ProductVariationPictures'] as $viaProductImage) {
                if (isset ($viaProductImage ['Id']) && (!isset ($seenRemoteImages [$viaProductImage ['Id']]) || $seenRemoteImages [$viaProductImage ['Id']] == false)) {
                    $this->client->send($this->oData->deleteObject($viaProductImage));
                    $this->logger->addDebug(__("Deleted product variation image"), ['Id' => $viaProductImage ['Id'], 'ImageUr' => $viaProductImage ['ImageUrl']]);
                    //$result->deleted++;
                }
            }
        }
    }

    /**
     * Export product images.
     *
     * @param MagentoProduct $product
     * @param array $viaProductVariation
     */
    private function exportProductVariationPicture(array $viaProductVariation, $imageUrl, $imagePos)
    {
        $forceHTTPMedia = $this->data->isForceHttpMedia();

        if (strlen($imageUrl) <= 0) {
            return null;
        }

        if ($forceHTTPMedia) {
            $imageUrl = str_replace('https://', 'http://', $imageUrl);
        }

        $viaProductImage = $this->oData->searchCollectionByKeyAndValue($viaProductVariation ['ProductVariationPictures'], ['ImagePosition' => $imagePos]);

        $viaProductImageDelta = array();

        $this->oData->updateDelta($viaProductImageDelta, $viaProductImage, 'ImageUrl', $imageUrl);
        $this->oData->updateDelta($viaProductImageDelta, $viaProductImage, 'ImagePosition', $imagePos);

        if ($viaProductImage == null) {
            $viaProductImageDelta ['ProductVariationId'] = $viaProductVariation ['Id'];
            $viaProductImage = $this->client->send($this->oData->saveObject('ProductVariationPictures', $viaProductImageDelta));
            $this->logger->addDebug(__("Added product variation image"), $viaProductImageDelta);
        } elseif (count($viaProductImageDelta)) {
            $this->client->send($this->oData->updateObject($viaProductImage, $viaProductImageDelta));
            $this->logger->addDebug(__("Updated product variation image"), $viaProductImageDelta);
        } else {
            // Nothing to do
        }

        return $viaProductImage;
    }

    /**
     * Export discount offer.
     *
     * @param array $viaProduct
     * @param array $exportedProducts
     * @return void
     */
    private function exportDiscountOffer(array $viaProduct, $exportedProducts)
    {
        $seenRemoteDiscountOffers = array();

        foreach ($exportedProducts as $productArray) {
            $viaVariation = $productArray ['via_variation'];
            $mappedValues = $productArray ['values'];

            if ($mappedValues ['DiscountOffer'] > 0) {
                $variationId = $viaVariation != null ? $viaVariation ['Id'] : null;

                $viaDiscountOffer = $this->oData->searchCollectionByKeyAndValue($viaProduct ['DiscountOffers'],
                    ['ProductId' => $viaProduct['Id'], 'ProductVariationId' => $variationId]);
                $viaDiscountOfferDelta = [];

                $this->oData->updateDelta($viaDiscountOfferDelta, $viaDiscountOffer, 'Price', ( string )( float )$mappedValues ['DiscountOffer']);

                if ($viaDiscountOffer == null) {
                    $viaDiscountOfferDelta ['ProductId'] = $viaProduct ['Id'];
                    if (is_numeric($variationId)) {
                        $viaDiscountOfferDelta ['ProductVariationId'] = $variationId;
                    }
                    $viaDiscountOfferDelta ['Type'] = 1; // 1 => Bisher/Ursprünglich
                    $viaDiscountOffer = $this->client->send($this->oData->saveObject('DiscountOffers', $viaDiscountOfferDelta));
                    $this->logger->addDebug(__("Added discount offer"), $viaDiscountOfferDelta);
                } elseif (count($viaDiscountOfferDelta)) {
                    $this->client->send($this->oData->updateObject($viaDiscountOffer, $viaDiscountOfferDelta));
                    $this->logger->addDebug(__("Updated discount offer"), $viaDiscountOfferDelta);
                } else {
                    // Nothing to do ;-)
                }

                $seenRemoteDiscountOffers [$viaDiscountOffer ['Id']] = $viaDiscountOffer;
            }
        }

        if (is_array($viaProduct ['DiscountOffers'])) {
            $this->deleteEntityIfIdHasNotBeenSeen($viaProduct ['DiscountOffers'], $seenRemoteDiscountOffers);
        }
    }

    /**
     * Export product categories.
     *
     * @param ProductInterface $product
     * @param array $viaProduct
     */
    private function exportProductCategories(ProductInterface $product, $viaProduct)
    {
        /* @var $product MagentoProduct */

        // Fetch all categories for product that are already synced with via
        $viaCategories = array();

        foreach ($product->getCategoryIds() as $magentoCategoryId) {
            $viaCategory = $this->categoryService->getViaCategoryById($magentoCategoryId);
            if ($viaCategory != null) {
                $viaCategories [] = $viaCategory;
            }
        }

        $seenRemoteCategories = array();
        // Add products to categories
        foreach ($viaCategories as $viaCatalog) {
            $pro = $this->oData->searchCollectionByKeyAndValue($viaProduct ['Catalogs'], ['Id' => $viaCatalog ['Id']]);
            if ($pro == null) {
                $this->logger->addDebug(__("Add product"), ['magentoProductId' => $product->getId(), 'viaProductId' => $viaProduct ['Id'], 'viaCatalogId' => $viaCatalog ['Id']]);
                $this->client->send($this->oData->addLink($viaProduct, 'Catalogs', $viaCatalog));
            } else {
            }
            $seenRemoteCategories [$viaCatalog ['Id']] = $viaCatalog;
        }

        // Remove products from old categories
        if (is_array($viaProduct ['Catalogs'])) {
            foreach ($viaProduct ['Catalogs'] as $viaCatalog) {
                if (isset ($viaCatalog ['Id']) && (!isset ($seenRemoteCategories [$viaCatalog ['Id']]) || $seenRemoteCategories [$viaCatalog ['Id']] == null)) {
                    $this->logger->addDebug(__("Delete product"), ['magentoProductId' => $product->getId(), "viaProductId" => $viaProduct ['Id'], 'viaCatalogId' => $viaCatalog ['Id']]);
                    $this->client->send($this->oData->deleteLink($viaProduct, 'Catalogs', $viaCatalog));
                }
            }
        }
    }

    /**
     * Export product images.
     *
     * @param MagentoProduct $product
     * @param array $viaProduct
     */
    private function exportProductImages(MagentoProduct $product, $viaProduct)
    {
        $seenRemoteImages = array();

        $images = $product->getMediaGalleryEntries();

        $imageUrls = array();

        if ($images != null) {
            $imagePos = 3;

            foreach ($images as $image) {
                /* @var $image ProductAttributeMediaGalleryEntryInterface */
                $smallOrImage = false;

                if ($image->isDisabled()) {
                    continue;
                }

                $imageUrl = $this->productImageHelper->init($product, $image->getId())->setImageFile($image->getFile())->getUrl();

                if (in_array('thumbnail', $image->getTypes())) {
                    $imageUrls[self::listingImageType] = $imageUrl;
                    $smallOrImage = true;
                }

                if (in_array('small_image', $image->getTypes())) {
                    $imageUrls[self::galleryImageType] = $imageUrl;
                    $smallOrImage = true;
                }

                if ($smallOrImage) {
                    continue;
                }

                if ($imagePos <= 12) {
                    $imageUrls[$imagePos++] = $imageUrl;
                } else {
                    $this->logger->addWarning(__('More than 10 images for product'), ['viaProductId' => $viaProduct ['Id']]);
                }
            }

            foreach ($imageUrls as $imagePos => $imageUrl) {
                $viaProductImage = $this->exportProductImage($viaProduct, $imageUrl, $imagePos);
                if (is_array($viaProductImage)) {
                    $seenRemoteImages [$viaProductImage ['Id']] = true;
                }
            }
        }

        if (is_array($viaProduct ['ProductImages'])) {
            foreach ($viaProduct ['ProductImages'] as $viaProductImage) {
                if (isset ($viaProductImage ['Id']) && (!isset ($seenRemoteImages [$viaProductImage ['Id']]) || $seenRemoteImages [$viaProductImage ['Id']] == false)) {
                    $this->client->send($this->oData->deleteObject($viaProductImage));
                    $this->logger->addDebug(__("Deleted product image"), ['Id' => $viaProductImage ['Id'], 'ImageUrl' => $viaProductImage ['ImageUrl']]);
                }
            }
        }
    }

    /**
     * Export single product image.
     *
     * @param array $viaProduct
     * @param string $imageUrl
     * @param int $type
     * @return array
     */
    private function exportProductImage(array $viaProduct, $imageUrl, $type)
    {
        if ($this->data->isForceHttpMedia()) {
            $imageUrl = str_replace('https://', 'http://', $imageUrl);
        }


        $viaProductImage = $this->oData->searchCollectionByKeyAndValue($viaProduct ['ProductImages'],
            ['ImageUrl' => $imageUrl, 'Type' => $type]);

        $viaProductImageDelta = array();

        $this->oData->updateDelta($viaProductImageDelta, $viaProductImage, 'ImageUrl', $imageUrl);
        $this->oData->updateDelta($viaProductImageDelta, $viaProductImage, 'ProductId', $viaProduct ['Id']);
        $this->oData->updateDelta($viaProductImageDelta, $viaProductImage, 'Type', $type);

        if ($viaProductImage == null) {
            $viaProductImage = $this->client->send($this->oData->saveObject('ProductImages', $viaProductImageDelta));
            $this->logger->addDebug(__("Added product image"), $viaProductImageDelta);
        } elseif (count($viaProductImageDelta)) {
            $this->client->send($this->oData->updateObject($viaProductImage, $viaProductImageDelta));
            $this->logger->addDebug(__("Updated product image"), $viaProductImageDelta);
        } else {
            // Nothing to do
        }

        return $viaProductImage;
    }

    /**
     * Update stock by product id.
     *
     * @param $productId
     * @param null $qtyStock
     * @return void
     * @internal param int $product
     * @internal param string $qty
     */
    public function updateStockById($productId, $qtyStock = null)
    {
        $product = $this->loadProductById($productId);
        if ($product) {
            $this->updateStock($product, $qtyStock);
        }
    }

    /**
     * Update stock by object.
     *
     * @param ProductInterface $product
     * @param int $qtyStock
     * @return void
     * @internal param int $qty
     */
    public function updateStock(ProductInterface $product, int $qtyStock = null)
    {
        if ($qtyStock === null) {
            $qtyStock = $this->viaProductHelper->getQtyStock($product);
        }

        $viaebayExportAttribute = $product->getCustomAttribute(self::VIAEBAY_EXPORT_ATTRIBUTE);
        if ($viaebayExportAttribute !== null) {
            $viaebayExport = $viaebayExportAttribute->getValue();
        } else {
            $viaebayExport = false;
        }

        try {
            $productExtension = $this->viaProductRepository->getByProductId($product->getId());
        } catch (NoSuchEntityException $e) {
            $productExtension = $this->viaProductFactory->create();
            $productExtension->setProductId($product->getId());
        }

        $viaebayId = $productExtension->getVIAeBayId();

        $criteria = $this->searchCriteriaBuilder->addFilter('product_id', $product->getId())->create();
        $productVariantExtensions = $this->viaProductVariationRepository->getList($criteria);

        try {
            if ($viaebayExport && $viaebayId) {
                $params = array(
                    'productId' => $viaebayId . 'L',
                    'stockAmount' => intval($qtyStock > 0 ? $qtyStock : 0)
                );

                $this->client->send($this->oData->call("ReviseInventoryStatus", $params));
            }

            foreach ($productVariantExtensions->getItems() as $productVariantExtension) {
                /* @var $productVariantExtension \VIAeBay\Connector\Api\Data\ProductVariationInterface */

                try {
                    $productExtension = $this->viaProductRepository->getById($productVariantExtension->getVIAeBayProductId());
                    if (!$productVariantExtension->getVIAeBayId()) {
                        continue;
                    }

                    $params = array(
                        'productId' => $productExtension->getVIAeBayId() . 'L',
                        'productVariationId' => $productVariantExtension->getVIAeBayId() . 'L',
                        'stockAmount' => intval($qtyStock > 0 ? $qtyStock : 0)
                    );

                    $this->client->send($this->oData->call("ReviseInventoryStatus", $params));
                } catch (\Exception $e) {
                    $this->logger->addError($e->getMessage());
                    $this->logger->addDebug($e->__toString());
                }
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage());
            $this->logger->addDebug($e->__toString());
        }
    }

    /**
     * Syncronize price and stock.
     */
    public function syncPriceAndStock()
    {
        $this->productBacklogResourceCollection->loadWithFilter();

        $criteria = $this->searchCriteriaBuilder->create();

        foreach ($this->viaProductRepository->getList($criteria) as $viaProduct) {
            /* @var $viaProduct \VIAeBay\Connector\Api\Data\ProductInterface */
            try {
                $product = $this->productRepository->getById($viaProduct->getProductId());
                $this->syncPriceAndStockSimpleProductCallback($product);
            } catch (NoSuchEntityException $e) {

            }
        }

        $criteria = $this->searchCriteriaBuilder->create();

        foreach ($this->viaProductVariationRepository->getList($criteria) as $viaProductVariation) {
            /* @var $viaProductVariation \VIAeBay\Connector\Api\Data\ProductVariationInterface */

            try {
                $product = $this->productRepository->getById($viaProductVariation->getProductId());
                $this->syncPriceAndStockConfigurableProductCallback($product);
            } catch (NoSuchEntityException $e) {

            }

        }
    }

    /**
     * Callback to sync stock and price for simple products.
     * see syncPriceAndStock.
     *
     * @param array $args
     */
    public function syncPriceAndStockSimpleProductCallback($args)
    {
        $result = $args ['result'];
        $viaEbayStoreId = $args ['viaEbayStoreId'];

        $productId = $args ['row'] ['entity_id'];
        $product = $this->loadProductById($productId);

        if (!$product || $product->getTypeId() != Type::TYPE_SIMPLE) {
            $this->logger->addDebug('Could not load product', ['id' => $productId]);
            return;
        }

        $mappedAttributes = $this->viaProductHelper->getMappedAttributeValues($product);
        $qtyStock = $this->viaProductHelper->getQtyStock($product);

        try {
            $productExtension = $this->viaProductRepository->getByProductId($product->getId());
        } catch (NoSuchEntityException $e) {
            $productExtension = $this->viaProductFactory->create();
            $productExtension->setProductId($product->getId());
        }

        $viaebayId = $productExtension->getVIAeBayId();

        if ($viaebayId) {
            try {
                $request = array(
                    'productId' => $viaebayId . 'L',
                    'stockAmount' => intval($qtyStock > 0 ? $qtyStock : 0),
                    'price' => $mappedAttributes ['Price'] . 'm'
                );

                if ($mappedAttributes ['DiscountOffer']) {
                    $request ['DiscountOffer'] = $mappedAttributes ['DiscountOffer'] . 'm';
                }

                $this->client->send($this->oData->call("ReviseInventoryStatus", $request));

                $result->product++;
            } catch (\Exception $e) {
                $this->logger->addError($e->getMessage());
                $this->logger->addDebug($e->__toString());
                $result->error++;
            }
        }
    }

    /**
     * Callback to sync stock and price for configurable products.
     * see syncPriceAndStock.
     *
     * @param array $args
     */
    public function syncPriceAndStockConfigurableProductCallback($args)
    {
        $viaEbayStoreId = $args ['viaEbayStoreId'];

        $productId = $args ['row'] ['entity_id'];
        $product = $this->loadProductById($productId);

        // Simple products get all variant ids assigned they belong to
        if (!$product || $product->getTypeId() != Type::TYPE_SIMPLE) {
            return;
        }

        try {
            $productExtension = $this->viaProductRepository->getByProductId($product->getId());
        } catch (NoSuchEntityException $e) {
            $productExtension = $this->viaProductFactory->create();
            $productExtension->setProductId($product->getId());
        }

        $criteria = $this->searchCriteriaBuilder->addFilter('product_id', $product->getId())->create();
        $productVariantExtensions = $this->viaProductVariationRepository->getList($criteria);

        foreach ($productVariantExtensions as $productVariantExtension) {
            /* @var $productVariantExtension \VIAeBay\Connector\Api\Data\ProductVariationInterface */
            $productVariant = $this->loadProductById($productVariantExtension->getProductId());

            if (!$productVariant) {
                continue;
            }

            $mappedAttributes = $this->viaProductHelper->getMappedAttributeValues($productVariant, $product);

            $qtyStock = $this->viaProductHelper->getQtyStock($product);

            try {
                $request = array(
                    'productId' => $productExtension->getVIAeBayId() . 'L',
                    'productVariationId' => $productVariantExtension->getVIAeBayId() . 'L',
                    'stockAmount' => intval($qtyStock > 0 ? $qtyStock : 0),
                    'price' => $mappedAttributes ['Price'] . 'm'
                );

                if ($mappedAttributes ['DiscountOffer']) {
                    $request ['DiscountOffer'] = $mappedAttributes ['DiscountOffer'] . 'm';
                }

                $this->client->send($this->oData->call("ReviseInventoryStatus", $request));
            } catch (\Exception $e) {
                $this->logger->addError($e->getMessage());
                $this->logger->addDebug($e->__toString());
            }
        }
    }

    /**
     * Get VIA products.
     *
     * @param string $filter
     * @throws \Exception
     * @return mixed
     */
    protected function getViaProducts($filter = null)
    {
        if ($this->viaProducts === null) {
            try {
                $request = new Request('GET', new Uri('Products?$expand=' . implode(',', self::productExpands) . ($filter == null ? '' : '&$filter=' . $filter)));
                $this->viaProducts = $this->client->send($request);
            } catch (ClientException $e) {
            }
        }

        return $this->viaProducts;
    }

    /**
     * Get VIA products.
     *
     * @param int $productId
     * @param string $filter
     * @return mixed
     */
    protected function getViaProduct(int $productId, $filter = null)
    {
        try {
            $request = new Request('GET', new Uri('Products(' . $productId . 'L)?$expand=' . implode(',', self::productExpands) . ($filter == null ? '' : '&$filter=' . $filter)));
            return $this->client->send($request);
        } catch (ClientException $e) {
            // Ignore errors
        }
        return null;
    }
}
