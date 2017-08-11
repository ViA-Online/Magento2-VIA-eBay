<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Plugin\Catalog\Api\ProductRepositoryInterface;

use Magento\Catalog\Api\Data\ProductExtensionFactory\Proxy as ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use VIAeBay\Connector\Api\ProductRepositoryInterface as VIAeBayProductRepositoryInterface;
use VIAeBay\Connector\Helper\Configuration;
use VIAeBay\Connector\Model\ProductFactory as VIAProductFactory;

class Plugin
{
    /**
     * @var ProductExtensionFactory
     */
    protected $productExtensionFactory;

    /**
     * @var VIAProductFactory
     */
    protected $viaProductFactory;

    /**
     * @var VIAeBayProductRepositoryInterface
     */
    protected $viaProductRepository;

    /**
     * @var Configuration
     */
    protected $configurationHelper;

    public function __construct(
        ProductExtensionFactory $productExtensionFactory,
        VIAProductFactory $productFactory,
        VIAeBayProductRepositoryInterface $viaProductRepository,
        Configuration $configuration
    )
    {
        $this->viaProductFactory = $productFactory;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->viaProductRepository = $viaProductRepository;
        $this->configurationHelper = $configuration;
    }

    public function afterGet
    (
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    )
    {
        if ($this->configurationHelper->isActive()) {
            $this->addVIAAttributesToProduct($entity);
        }

        return $entity;
    }

    public function afterGetById
    (
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    )
    {
        if ($this->configurationHelper->isActive()) {
            $this->addVIAAttributesToProduct($entity);
        }

        return $entity;
    }

    public function afterSave
    (
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    )
    {
        $extensionAttributes = $entity->getExtensionAttributes();

        if ($this->configurationHelper->isActive() && $extensionAttributes) {
            $viaProduct = $extensionAttributes->getVIAeBayConnector();
            $viaProduct->setProductId($entity->getId());
            $this->viaProductRepository->save($viaProduct);
        }

        return $entity;
    }

    public function afterGetList(
        ProductRepositoryInterface $subject,
        ProductSearchResultsInterface $entity
    )
    {
        if ($this->configurationHelper->isActive()) {
            foreach ($entity as $item) {
                $this->addVIAAttributesToProduct($item);
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @return self
     */
    private function addVIAAttributesToProduct(ProductInterface $product)
    {
        $extensionAttributes = $product->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->productExtensionFactory->create();
        }
        try {
            $viaProduct = $this->viaProductRepository->getByProductId($product->getId());
        } catch (NoSuchEntityException $e) {
            $viaProduct = $this->viaProductFactory->create();
        }
        $extensionAttributes->setViaebayConnector($viaProduct);
        $product->setExtensionAttributes($extensionAttributes);
        return $this;
    }
}