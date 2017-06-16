<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Model\Product;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use VIAeBay\Connector\Model\ProductRepository as VIAProductRepository;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
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
        $entityExtension = $entity->getExtensionAttributes();
        $viaProduct = $this->viaProductRepository->getByProductId($entity->getId());
        if ($viaProduct) {
            $entityExtension->setViaebayConnector($viaProduct);
        }
        $entity->setExtensionAttributes($entityExtension);
        return $entity;
    }
}
