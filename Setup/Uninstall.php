<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Setup;


use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Sales\Model\Order;


class Uninstall implements UninstallInterface
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
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Invoked when remove-data flag is set during module uninstall.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->getConnection()->dropTable($setup->getTable('viaebay_backlog_product'));
        $setup->getConnection()->dropTable($setup->getTable('viaebay_order'));

        $setup->endSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create();

        $eavSetup->removeAttribute(Category::ENTITY, 'viaebay_id');

        $eavSetup->removeAttribute(Product::ENTITY, 'viaebay_id');

        $eavSetup->removeAttribute(Product::ENTITY, 'viaebay_export');

        $eavSetup->removeAttribute(Product::ENTITY, 'viaebay_shipping_profile_id');

        $eavSetup->removeAttribute(Product::ENTITY, 'viaebay_auto_accept_price');

        $eavSetup->removeAttribute(Product::ENTITY, 'viaebay_auto_decline_price');

        $eavSetup->removeAttribute(Order::ENTITY, 'viaebay_id');

        $eavSetup->removeAttribute(Customer::ENTITY, 'viaebay_buyer_id');

        $eavSetup->removeAttribute(Customer::ENTITY, 'viaebay_buyer_name');
    }
}