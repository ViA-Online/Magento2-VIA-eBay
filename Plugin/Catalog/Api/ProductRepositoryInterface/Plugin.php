<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Plugin\Catalog\Api\ProductRepositoryInterface;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use VIAeBay\Connector\Api\ProductRepositoryInterface as VIAeBayProductRepositoryInterface;
use VIAeBay\Connector\Model\ProductFactory as VIAProductFactory;

class Plugin
{
    protected $productExtensionFactory;
    protected $viaProductFactory;
    protected $viaProductRepository;

    public function __construct(
        ProductExtensionFactory $productExtensionFactory,
        VIAProductFactory $productFactory,
        VIAeBayProductRepositoryInterface $viaProductRepository
    )
    {
        $this->viaProductFactory = $productFactory;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->viaProductRepository = $viaProductRepository;
    }

    public function afterGet
    (
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    )
    {
        $this->addVIAAttributesToProduct($entity);

        return $entity;
    }

    public function afterGetById
    (
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    )
    {
        $this->addVIAAttributesToProduct($entity);

        return $entity;
    }

    public function afterSave
    (
        ProductRepositoryInterface $subject,
        ProductInterface $entity
    )
    {
        $extensionAttributes = $entity->getExtensionAttributes();

        if ($extensionAttributes) {
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
        foreach ($entity as $item) {
            $this->addVIAAttributesToProduct($item);
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