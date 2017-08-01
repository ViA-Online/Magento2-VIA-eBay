<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type\Simple as SimpleType;
use Magento\Catalog\Model\Product\Type\Virtual as VirtualType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableTypeResource;
use VIAeBay\Connector\Logger\Logger;
use VIAeBay\Connector\Model\Backlog\Product as BacklogProduct;
use VIAeBay\Connector\Model\Backlog\ProductFactory as BacklogProductFactory;
use VIAeBay\Connector\Model\ResourceModel\Backlog\Product as BacklogProductResource;

class Backlog
{
    /**
     * @var BacklogProductResource
     */
    private $backlogProductResource;

    /**
     * @var BacklogProductFactory
     */
    private $backlogProductFactory;

    /**
     * @var ConfigurableTypeResource
     */
    private $configurableTypeResource;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Allow to disable backlog creation during sync.
     *
     * @var boolean
     */
    private $disabled;

    /**
     * Backlog constructor.
     * @param BacklogProductResource $backlogProductResource
     * @param BacklogProductFactory $backlogProductFactory
     * @param ConfigurableTypeResource $configurableTypeResource
     */
    function __construct(BacklogProductResource $backlogProductResource, BacklogProductFactory $backlogProductFactory,
                         ConfigurableTypeResource $configurableTypeResource, Logger $logger)
    {
        $this->backlogProductResource = $backlogProductResource;
        $this->backlogProductFactory = $backlogProductFactory;
        $this->configurableTypeResource = $configurableTypeResource;
        $this->logger = $logger;
    }

    public function createBacklog(ProductInterface $product)
    {
        if ($this->disabled) {
            $this->logger->info(__('Backlog creation disabled'));
            return;
        }

        if (!is_numeric($product->getId()) || $product->getId() <= 0) {
            $this->logger->addWarning(__('Cannot create backlog for product without id'));
            return;
        }

        $diff = $this->diff($product->getData(), $product->getOrigData());

        $this->createBacklogByProductId($product->getId(), json_encode($diff));

        $instance = $product->getTypeInstance();

        if ($instance instanceof SimpleType || $instance instanceof VirtualType) {
            $configurableParents = $this->configurableTypeResource->getParentIdsByChild($product->getId());

            $parents = array_unique(array_merge($configurableParents));

            foreach ($parents as $productId) {
                $this->createBacklogByProductId($productId, '_parent_');
            };
        } else if ($instance instanceof ConfigurableType) {
            foreach ($instance->getUsedProducts($product) as $childProduct) {
                $this->createBacklogByProductId($childProduct->getId());
            }
        }
    }

    /**
     * @param array $old
     * @param array $new
     * @return array
     */
    protected function diff(array $old, array $new)
    {
        $result = [];

        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $old) || $value === $old[$key]) {
                $result[] = $key;
            }
        }

        return $result;
    }

    public function createBacklogByProductId(int $productId, string $changes = null)
    {
        /**
         * @var $backlogProduct BacklogProduct
         */
        $backlogProduct = $this->backlogProductFactory->create();
        $backlogProduct->setData('product_id', $productId);
        $backlogProduct->setData('changes', $changes);

        $this->backlogProductResource->save($backlogProduct);
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param bool $disabled
     */
    public function setDisabled(bool $disabled)
    {
        $this->disabled = $disabled;
    }
}
