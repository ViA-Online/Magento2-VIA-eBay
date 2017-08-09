<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Setup;


use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory.
     *
     * @var $eavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
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
        $setup->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

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
                'type' => 'decimal',
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
                'type' => 'decimal',
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

        $setup->endSetup();
    }
}