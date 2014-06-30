<?php

require_once __DIR__.'/base.php';

/**
 * Controller_Authのテスト
 */
class Test_Controller_Auth extends Test_Controller_Base
{
	function test__init_を呼ぶとclientにはクライアントのインスタンスがセットされている() {
		$ref = new ReflectionClass('Controller_Auth');
		$client = $ref->getProperty('client');
		$client->setAccessible(true);

		$this->assertInstanceOf('\NextEngine\Api\Client', $client->getValue());
	}
	// function test__init_() {}
	// function test__init_() {}

	function test_action_login_() {}
	// function test_action_login_() {}
	// function test_action_login_() {}

	function test_action_logout_() {}
	// function test_action_logout_() {}
	// function test_action_logout_() {}

	function test_action_callback_() {}
	// function test_action_callback_() {}
	// function test_action_callback_() {}

}
