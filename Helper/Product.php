<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Helper;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use VIAeBay\Connector\Helper\Attribute as AttributeHelper;
use VIAeBay\Connector\Helper\Configuration as ConfigurationHelper;
use VIAeBay\Connector\Model\Attribute\Source\Mapping;
use VIAeBay\Connector\Model\AttributeRepository as AttributeRepository;

class Product
{
    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var Mapping
     */
    private $mapping;

    /**
     * @var ConfigurationHelper
     */
    private $configurationHelper;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * Product constructor.
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     * @param StockRegistryInterface $stockRegistry
     * @param Mapping $mapping
     * @param AttributeHelper $attributeHelper
     * @param Configuration $configurationHelper
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        ExtensionAttributesFactory $extensionAttributesFactory,
        StockRegistryInterface $stockRegistry,
        Mapping $mapping,
        AttributeHelper $attributeHelper,
        ConfigurationHelper $configurationHelper,
        AttributeRepository $attributeRepository
    ) {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->stockRegistry = $stockRegistry;
        $this->_attributeHelper = $attributeHelper;
        $this->mapping = $mapping;
        $this->configurationHelper = $configurationHelper;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductExtensionInterface|null
     */
    public function getExtensionAttributes(ProductInterface $product)
    {
        $attributes = $product->getExtensionAttributes();

        if ($attributes == null) {
            /* @var $attributes ProductExtensionInterface */
            $attributes = $this->extensionAttributesFactory->create('Magento\Catalog\Api\Data\ProductInterface');
            $product->setExtensionAttributes($attributes);
        }

        return $attributes;
    }

    /**
     * Get stock qty for given product.
     *
     * @param ProductInterface $product
     * @param StockItemInterface $stockItem
     * @return float|int
     */
    public function getQtyStock(ProductInterface $product, StockItemInterface $stockItem = null)
    {
        if ($stockItem == null) {
            $stockItem = $this->stockRegistry->getStockItem(
                $product->getId(),
                $this->configurationHelper->getStoreId()
            );
        }

        $qtyStock = 0;

        if ($stockItem->getManageStock()) {
            if ($stockItem->getIsInStock() && $stockItem->getQty() > 0) {
                $qtyStock = $stockItem->getQty();
            }
        }

        if ($qtyStock < 0) {
            $qtyStock = 0;
        }

        return $qtyStock;
    }

    /**
     * Extract mapped attributes from Product.
     *
     * @param \Magento\Catalog\Model\Product $product to extract mapped values from.
     * @param ProductInterface $parentProduct
     * @return array of name => value
     */
    public function getMappedAttributeValues(\Magento\Catalog\Model\Product $product, ProductInterface $parentProduct = null)
    {
        $useConfigurablePrice = $this->configurationHelper->isUseConfigurableProductPrice();

        $result = [];

        // Initialize mappings
        foreach ($this->mapping->getAllOptions() as $value) {
            $result [$value['value']] = null;
        }

        $result ['_id'] = $product->getId();
        if ($parentProduct != null) {
            $result ['_parent_id'] = $parentProduct->getId();
        }

        // Default price
        $result ['Price'] = $product->getPrice();
        $result ['DiscountOffer'] = $product->getSpecialPrice();

        foreach ($product->getAttributes() as $attribute) {
            $viaEbayAttributes = $this->attributeRepository->getByCode($attribute->getAttributeCode());

            if (empty($viaEbayAttributes)) {
                continue;
            }

            foreach ($viaEbayAttributes as $viaEbayAttribute) {
                switch ($viaEbayAttribute->getType()) {
                    case 'Price':
                    case 'DiscountOffer':
                        $price = $attribute->getFrontend()->getValue($product);

                        if (($price <= null || $useConfigurablePrice) && $parentProduct != null
                            && $parentProduct instanceof DataObject) {
                            $result [$viaEbayAttribute->getType()] = $attribute->getFrontend()->getValue($parentProduct);
                        } else {
                            $result [$viaEbayAttribute->getType()] = $price;
                        }
                        break;
                    case 'None':
                    case 'CustomAttribute':
                    case 'OptionalAttribute':
                        // Ignored
                        break;
                    default:
                        $result [$viaEbayAttribute->getType()] = $attribute->getFrontend()->getValue($product);
                        break;
                }
            }
        }
        return $result;
    }
}
