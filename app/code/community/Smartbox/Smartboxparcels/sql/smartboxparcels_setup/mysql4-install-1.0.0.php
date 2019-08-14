<?php

$installer = $this;

$installer->startSetup();
$installer->run("SET FOREIGN_KEY_CHECKS=0");
$installer->run("
	CREATE TABLE IF NOT EXISTS {$this->getTable('order_shipping_smartboxparcels')} (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `order_id` int(11) NOT NULL,
	  `parcel_id` varchar(200) NOT NULL default '',
	  `tracking_number` varchar(200) NOT NULL default '',
	  `parcel_status` varchar(200) NOT NULL default '',
	  `parcel_detail` text NOT NULL default '',
	  `parcel_target_machine_id` varchar(200) NOT NULL default '',
	  `parcel_target_machine_detail` text NOT NULL default '',
      `creation_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `api_source` varchar(200) NOT NULL default '',
	  `api_key` varchar(5) NOT NULL default '',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->run("SET FOREIGN_KEY_CHECKS=1");

$installer->endSetup();