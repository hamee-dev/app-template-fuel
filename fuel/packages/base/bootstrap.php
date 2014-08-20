<?php

Autoloader::add_namespace('Base', __DIR__.'/classes/');

Autoloader::add_classes(array(
	'Base\\Controller_Base' => __DIR__ . '/classes/controller/base.php',
	'Base\\Controller_Auth' => __DIR__ . '/classes/controller/auth.php',

	'Base\\Logger'          => __DIR__ . '/classes/logger.php',

));
