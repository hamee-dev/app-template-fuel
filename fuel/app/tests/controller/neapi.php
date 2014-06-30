<?php

require_once __DIR__.'/base.php';

/**
 * Controller_Neapiのテスト
 */
class Test_Controller_Neapi extends Test_Controller_Base
{
	function setUp() {
		parent::setUp();

		// CLIからだとセッションが何も設定されてないので明示的にセット
		$user = Model_User::find(1);

		$user_key = Config::get('session.keys.ACCOUNT_USER');
		Session::set($user_key, $user);
	}

	function test_init_を呼ぶとclientにはクライアントのインスタンスがセットされている() {
		$ref = new ReflectionClass('Controller_Neapi');
		$client = $ref->getProperty('client');
		$client->setAccessible(true);

		$this->assertInstanceOf('\NextEngine\Api\Client', $client->getValue());
	}
}
