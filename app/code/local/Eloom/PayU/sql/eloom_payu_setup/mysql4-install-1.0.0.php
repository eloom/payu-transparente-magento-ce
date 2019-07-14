<?php

##eloom.licenca##

$installer = $this;
$installer->startSetup();
$conn = $installer->getConnection();

$salesOrderTable = $installer->getTable('sales/order');
if (!$conn->tableColumnExists($salesOrderTable, 'payu_discount_amount')) {
  $conn->addColumn($salesOrderTable, 'payu_discount_amount', 'DECIMAL(10,4) NOT NULL');
}
if (!$conn->tableColumnExists($salesOrderTable, 'payu_base_discount_amount')) {
  $conn->addColumn($salesOrderTable, 'payu_base_discount_amount', 'DECIMAL(10,4) NOT NULL');
}

$quoteTableAddress = $installer->getTable('sales/quote_address');
if (!$conn->tableColumnExists($quoteTableAddress, 'payu_discount_amount')) {
  $conn->addColumn($quoteTableAddress, 'payu_discount_amount', 'DECIMAL(10,4) NOT NULL');
}
if (!$conn->tableColumnExists($quoteTableAddress, 'payu_base_discount_amount')) {
  $conn->addColumn($quoteTableAddress, 'payu_base_discount_amount', 'DECIMAL(10,4) NOT NULL');
}

$invoiceTable = $installer->getTable('sales/invoice');
if (!$conn->tableColumnExists($invoiceTable, 'payu_discount_amount')) {
  $conn->addColumn($invoiceTable, 'payu_discount_amount', 'DECIMAL(10,4) NOT NULL');
}
if (!$conn->tableColumnExists($invoiceTable, 'payu_base_discount_amount')) {
  $conn->addColumn($invoiceTable, 'payu_base_discount_amount', 'DECIMAL(10,4) NOT NULL');
}

$installer->endSetup();
