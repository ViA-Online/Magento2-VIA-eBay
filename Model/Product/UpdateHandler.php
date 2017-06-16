<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\Product;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use VIAeBay\Connector\Model\ProductRepository as VIAProductRepository;

/**
 * Class UpdateHandler
 */
class UpdateHandler implements ExtensionInterface
{
    /**
     * @var VIAProductRepository
     */
    protected $viaProductRepository;

    /**
     * @param VIAProductRepository $viaProductRepository
     */
    public function __construct(
        VIAProductRepository $viaProductRepository)
    {
        $this->viaProductRepository = $viaProductRepository;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var \VIAeBay\Connector\Api\Data\ProductInterface $viaProduct */
        $viaProduct = $entity->getExtensionAttributes()->getViaebayConnector() ?: null;
        if ($viaProduct != null) {
            $this->viaProductRepository->save($viaProduct);
        }

        return $entity;
    }
}
