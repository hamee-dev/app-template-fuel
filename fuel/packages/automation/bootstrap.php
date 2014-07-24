<?php

Autoloader::add_core_namespace('Automation');

Autoloader::add_classes(array(
	'Automation\\Automation' => __DIR__ . '/classes/automation.php',
	'Automation\\AutomationException' => __DIR__ . '/classes/automationexception.php',

	'Automation\\Service' => __DIR__ . '/classes/service.php',
	'Automation\\Automation_Driver' => __DIR__ . '/classes/automation/driver.php',
));
