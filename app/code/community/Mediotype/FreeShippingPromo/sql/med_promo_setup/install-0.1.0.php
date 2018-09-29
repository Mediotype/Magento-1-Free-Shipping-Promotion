<?php
/**
 * add creates free shipping flag to coupon in db
 *
 * @author  Joel    @Mediotype
 */ 
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn(
    $this->getTable('salesrule'),
    'creates_free_shipping',
    "tinyint(1) unsigned not null default '0' after simple_free_shipping"
);

$installer->endSetup();