<?php

Autoloader::add_core_namespace('Nextengine');

Autoloader::add_classes(array(
	'Nextengine\\Api\\NextengineApiException' => __DIR__ . '/classes/nextengine/api/client.php',
	'Nextengine\\Api\\Client' => __DIR__ . '/classes/nextengine/api/client.php',
	'Nextengine\\Api\\Router' => __DIR__ . '/classes/nextengine/api/client/router.php',
	'Nextengine\\Api\\Batch' => __DIR__ . '/classes/nextengine/api/client/batch.php',

));
