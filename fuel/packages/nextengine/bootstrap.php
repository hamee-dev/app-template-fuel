<?php

Autoloader::add_core_namespace('Nextengine');

Autoloader::add_classes(array(
	'Nextengine\\NextengineApiException' => __DIR__ . '/classes/nextengine/api.php',
	'Nextengine\\Nextengine_Api' => __DIR__ . '/classes/nextengine/api.php',

));
