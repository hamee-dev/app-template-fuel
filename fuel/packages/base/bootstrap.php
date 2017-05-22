<?php

Autoloader::add_namespace('Base', __DIR__.'/classes/');

Autoloader::add_classes(array(
	'Base\\Controller_Base'  => __DIR__ . '/classes/controller/base.php',
	'Base\\Controller_Auth'  => __DIR__ . '/classes/controller/auth.php',
	'Base\\Controller_Error' => __DIR__ . '/classes/controller/error.php',
	'Base\\Controller_Neapi' => __DIR__ . '/classes/controller/neapi.php',

	'Base\\Model_Base'       => __DIR__ . '/classes/model/base.php',
	'Base\\Model_User'       => __DIR__ . '/classes/model/user.php',
	'Base\\Model_Company'    => __DIR__ . '/classes/model/company.php',

	'Base\\Logger'           => __DIR__ . '/classes/logger.php',

	'Base\\Runner'           => __DIR__ . '/tasks/base.php',

));
