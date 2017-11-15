<?php
/**
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

namespace VIAeBay\Connector\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable($setup->getTable('viaebay_category'))
            ->addColumn(
                'viaebay_category_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'VIA-Connect Category Id')
            ->addColumn(
                'viaebay_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'VIA-Connect ID'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At')
            ->addForeignKey(
                $setup->getFkName(
                    'viaebay_category',
                    'viaebay_category_id',
                    'catalog_category_entity',
                    'entity_id'
                ),
                'viaebay_category_id',
                $setup->getTable('catalog_category_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment(
                'Catalog Category Extension'
            );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable($setup->getTable('viaebay_product'))
            ->addColumn(
                'viaebay_product_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'VIA-Connect Product Id')
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'viaebay_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'VIA-Connect ID'
            )
            ->addColumn(
                'export',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => true],
                'Enabled'
            )
            ->addColumn(
                'shipping_profile_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Enabled'
            )
            ->addColumn(
                'auto_accept_price',
                Table::TYPE_DECIMAL,
                null,
                ['nullable' => true],
                'Enabled'
            )
            ->addColumn(
                'auto_decline_price',
                Table::TYPE_DECIMAL,
                null,
                ['nullable' => true],
                'Enabled'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At')
            ->addForeignKey(
                $setup->getFkName(
                    'viaebay_product',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment(
                'Catalog Product Extension'
            );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable('viaebay_product_variation')->addColumn(
            'viaebay_product_variation_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
            'Entity ID')
            ->addColumn(
                'viaebay_product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Variation Product Id'
            )
            ->addColumn(
                'viaebay_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'VIA-Connect ID'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At')
            ->addForeignKey(
                $setup->getFkName(
                    'viaebay_product_variation',
                    'viaebay_product_id',
                    'viaebay_product',
                    'viaebay_product_id'
                ),
                'viaebay_product_id',
                $setup->getTable('viaebay_product'),
                'viaebay_product_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    'viaebay_product_variation',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment(
                'Catalog Product Variation Extension'
            );
        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable($setup->getTable('viaebay_customer'))
            ->addColumn(
                'viaebay_customer_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'buyer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Buyer Id'
            )
            ->addColumn(
                'buyer_name',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => true],
                'Buyer Name'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addForeignKey(
                $setup->getFkName(
                    'viaebay_customer',
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                ),
                'customer_id',
                $setup->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Additional Customer Data');

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable($setup->getTable('viaebay_order'))
            ->addColumn(
                'viaebay_order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'VIA-Connect Order Id'
            )
            ->addColumn(
                'magento_order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Magento Order Id'
            )
            ->addColumn(
                'platform_order_id',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => true],
                'Platform Order Id'
            )
            ->addColumn(
                'buyer_name',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => true],
                'Buyer Name'
            )
            ->addColumn(
                'message',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => true],
                'Message'
            )
            ->addColumn(
                'error',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => true],
                'Error'
            )
            ->addColumn(
                'checkout_complete_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Checkout Completed Date'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addForeignKey(
                $setup->getFkName(
                    'viaebay_order',
                    'magento_order_id',
                    'sales_order',
                    'entity_id'
                ),
                'magento_order_id',
                $setup->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Additional Order Data');

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable($setup->getTable('viaebay_attribute'))
            ->addColumn(
                'viaebay_attribute_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Attribute Id'
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => true],
                'Error'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addForeignKey(
                $setup->getFkName(
                    'viaebay_attribute',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Additional Attribute Data');

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable($setup->getTable('viaebay_backlog_product'))
            ->addColumn(
                'viaebay_backlog_product_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'VIA-Connect Backlog Product Id')
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'changes',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => true],
                'Changes'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At')
            ->addForeignKey(
                $setup->getFkName(
                    'viaebay_backlog_product',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment(
                'Backlog for Uploads'
            );

        $setup->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
