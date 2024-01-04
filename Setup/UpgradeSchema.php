<?php

namespace Ecloud\Integrations\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            if (!$installer->tableExists('ecloud_integrations_restriction')) {
                $table = $installer
                    ->getConnection()
                    ->newTable($installer->getTable('ecloud_integrations_restriction'))
                    ->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
                    )
                    ->addColumn(
                        'active',
                        Table::TYPE_BOOLEAN,
                        null,
                        ['nullable' => false],
                        'Activate restriction'
                    )
                    ->addColumn(
                        'entity_id',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => true],
                        'Magento entity ID to which the restriction applies'
                    )
                    ->addColumn(
                        'erp_entity_id',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => true],
                        'ERP entity ID to which the restriction applies'
                    )
                    ->addColumn(
                        'integration_name',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false],
                        'Integration name for the restriction'
                    )
                    ->addColumn(
                        'reason',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false],
                        'Reason to apply the restriction'
                    )
                    ->addColumn(
                        'comment',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => true],
                        'Comment of the restriction'
                    )
                    ->addColumn(
                        'restriction_date',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                        'Date on which the restriction was created'
                    )
                    ->setComment('Ecloud integrations restrictions');
                $installer->getConnection()->createTable($table);
            }
        }

        $installer->endSetup();
    }
}
