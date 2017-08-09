<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use VIAeBay\Connector\Model\AttributeFactory\Proxy as VIAeBayAttributeFactory;
use VIAeBay\Connector\Model\ResourceModel\Attribute\Proxy as VIAeBayAttributeResource;


class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory.
     *
     * @var $eavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var VIAeBayAttributeResource
     */
    private $viaAttributeResource;

    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var VIAeBayAttributeFactory
     */
    private $viaAttributeFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param VIAeBayAttributeResource $viaAttributeResource
     * @param VIAeBayAttributeFactory $viaAttributeFactory
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        VIAeBayAttributeResource $viaAttributeResource,
        VIAeBayAttributeFactory $viaAttributeFactory,
        AttributeRepository $attributeRepository)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->viaAttributeResource = $viaAttributeResource;
        $this->viaAttributeFactory = $viaAttributeFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        //install data here

        $setup->startSetup();

        $mapping = [
            'name' => 'Title',
            'price' => 'Price',
            'description' => 'Description',
            'short_description' => 'ShortDescription'
        ];

        foreach ($mapping as $key => $value) {
            $viaAttribute = $this->viaAttributeFactory->create();

            /* @var $viaAttribute \VIAeBay\Connector\Model\Attribute */
            $this->viaAttributeResource->load($viaAttribute, $value, 'type');

            if (!$viaAttribute->getId()) {
                $magentoAttribute = $this->attributeRepository->get(Product::ENTITY, $key);
                $viaAttribute->setData('attribute_id', $magentoAttribute->getAttributeId());
                $viaAttribute->setData('type', $value);

                $this->viaAttributeResource->save($viaAttribute);
            } else {
                // Already existing
            }
        }

        $setup->endSetup();
    }
}
