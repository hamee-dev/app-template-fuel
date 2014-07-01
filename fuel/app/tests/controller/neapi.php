<?php

require_once __DIR__.'/base.php';
require_once __DIR__.'/../usedb.php';

/**
 * Controller_Neapiのテスト
 */
class Test_Controller_Neapi extends Usedb
{
	function setUp() {
		// CLIだとundefined indexと言われるので明示的にnullを設定
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = null;

		Config::set('language', null);

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
