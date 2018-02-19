<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module
 * to newer versions in the future.
 *
 * @copyright 2017 La Poste
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace LaPoste\ColissimoFrontPage\Setup;

use LaPoste\ColissimoFrontPage\Api\Data\TransactionInterface;
use LaPoste\ColissimoFrontPage\Helper\Config;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install setup.
 *
 * @author Smile (http://www.smile.fr)
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->alterSalesOrderTable($setup);
        $setup->endSetup();
    }

    /**
     * Add Colissimo data column to the order table.
     *
     * @param SchemaSetupInterface $setup
     */
    protected function alterSalesOrderTable($setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('sales_order'),
                Config::FIELD_COLISSIMO_RELAY_ADDRESS_DATA,
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Colissimo Relay Address Data',
                    'nullable' => true,
                ]
            );
    }
}
