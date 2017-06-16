<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Setup;


use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use VIAeBay\Connector\Model\AttributeFactory as VIAeBayAttributeFactory;
use VIAeBay\Connector\Model\AttributeRepository as VIAeBayAttributeRepository;
use VIAeBay\Connector\Model\ResourceModel\Attribute as VIAeBayAttributeResource;

class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory.
     *
     * @var $eavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * EAV setup factory.
     *
     * @var $eavSetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var VIAeBayAttributeResource
     */
    private $viaAttributeResource;
    /**
     * @var VIAeBayAttributeRepository
     */
    private $viaAttributeRepository;
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var VIAeBayAttributeFactory
     */
    private $viaAttributeFactory;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CategorySetupFactory $categorySetupFactory,
        VIAeBayAttributeResource $viaAttributeResource,
        VIAeBayAttributeRepository $viaAttributeRepository,
        VIAeBayAttributeFactory $viaAttributeFactory,
        AttributeRepository $attributeRepository,
        TypeListInterface $cacheTypeList)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->viaAttributeRepository = $viaAttributeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->viaAttributeResource = $viaAttributeResource;
        $this->viaAttributeFactory = $viaAttributeFactory;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        $setup->startSetup();

        $eavSetup->addAttribute(
            Category::ENTITY,
            'viaebay_id',
            [
                'label' => 'VIA-eBay Catalog Id',
                'group' => 'VIA-eBay',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'type' => 'int',
                'input' => 'text',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'sort_order' => 100
            ]
        );

        /*
        $eavSetup->addAttribute(
            Product::ENTITY,
            'viaebay_id',
            [
                'label' => 'VIA-eBay Product Id',
                'group' => 'VIA-eBay',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'type' => 'int',
                'input' => 'text',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'sort_order' => 100,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );
        */

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viaebay_export',
            [
                'label' => 'Export to VIA-eBay',
                'group' => 'VIA-eBay',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'input' => 'select',
                'input_renderer' => 'Magento\GiftMessage\Block\Adminhtml\Product\Helper\Form\Config',
                'type' => 'text',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'sort_order' => 101,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viaebay_shipping_profile_id',
            [
                'label' => 'VIA-eBay Shipping Profile',
                'group' => 'VIA-eBay',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'type' => 'int',
                'input' => 'text',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'sort_order' => 102,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viaebay_auto_accept_price',
            [
                'label' => 'BestOffer Auto Accept Price',
                'group' => 'VIA-eBay',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'type' => 'price',
                'input' => 'price',
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'sort_order' => 103,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viaebay_auto_decline_price',
            [
                'label' => 'BestOffer Auto Decline Price',
                'group' => 'VIA-eBay',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'type' => 'price',
                'input' => 'price',
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Price',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'sort_order' => 104,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
            ]
        );

        /*
        $eavSetup->addAttribute(
            Product::ENTITY,
            'viaebay_variant_ids',
            [
                'label' => 'VIA-eBay Variant IDs',
                'group' => 'VIA-eBay',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'type' => 'varchar',
                'input' => 'text',
                'backend' => 'VIAeBay\Connector\Model\Entity\Attribute\Backend\Json',
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'sort_order' => 105,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
        */

        /*
        $eavSetup->addAttribute(
            Order::ENTITY,
            'viaebay_id',
            [
                'label' => 'VIA-eBay Order Id',
                'group' => 'VIA-eBay',
                'type' => 'int',
                'required' => false,
                'visible' => true,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]);
        */

        $eavSetup->addAttribute(
            Customer::ENTITY,
            'viaebay_id',
            [
                'label' => 'VIA-eBay Buyer Id',
                'group' => 'VIA-eBay',
                'type' => 'int',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true
            ]
        );

        $eavSetup->addAttribute(
            Customer::ENTITY,
            'viaebay_name',
            [
                'label' => 'VIA-eBay Buyer Name',
                'group' => 'VIA-eBay',
                'type' => 'varchar',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true
            ]
        );

        $eavSetup->cleanCache();

        $mapping = [
            'name' => 'Title',
            'price' => 'Price',
            'description' => 'Description',
            'short_description' => 'ShortDescription'
        ];

        foreach ($mapping as $key => $value) {
            $current = $this->viaAttributeResource->loadByAttributeCode($key);
            if (!is_array($current) || !empty($current)) {
                $magentoAttribute = $this->attributeRepository->get(Product::ENTITY, $key);
                $viaAttribute = $this->viaAttributeFactory->create();
                $viaAttribute->setData('attribute_id', $magentoAttribute->getAttributeId());
                $viaAttribute->setData('type', $value);
                $viaAttribute->getResource()->save($viaAttribute);
            } else {
                // Already existing
            }
        }

        $setup->endSetup();
    }
}