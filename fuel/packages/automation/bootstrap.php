<?php

Autoloader::add_core_namespace('Automation');

Autoloader::add_classes(array(
	'Automation\\Automation'          => __DIR__ . '/classes/automation.php',
	'Automation\\AutomationException' => __DIR__ . '/classes/automationexception.php',

	'Automation\\Batch'               => __DIR__ . '/classes/batch.php',
	'Automation\\Batch\\Goods'        => __DIR__ . '/classes/batch/goods.php',
	'Automation\\Batch\\Stock'        => __DIR__ . '/classes/batch/stock.php',
	'Automation\\Batch\\Recieved'     => __DIR__ . '/classes/batch/recieved.php',

	'Automation\\Service'             => __DIR__ . '/classes/service.php',

	'Automation\\Driver'              => __DIR__ . '/classes/driver.php',
	'Automation\\Auth\\Driver'        => __DIR__ . '/classes/auth/driver.php',
	'Automation\\Goods\\Driver'       => __DIR__ . '/classes/goods/driver.php',
	'Automation\\Recieved\\Driver'    => __DIR__ . '/classes/recieved/driver.php',
	'Automation\\Stock\\Driver'       => __DIR__ . '/classes/stock/driver.php',
));
