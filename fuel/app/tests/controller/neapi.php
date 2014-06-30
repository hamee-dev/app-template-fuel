<?php

/**
 * Controller_Neapiのテスト
 */
class Test_Controller_Neapi extends Test_Controller_Base
{
	function setUp() {
		parent::setUp();

		// CLIからだとセッションが何も設定されてないので明示的にセット
		// FIXME: ハードコードではなく、ユーザ情報のシードをテスト時に投入してそれを利用したい
		$user = (object)array(
			'id'  => 20,
			'uid' => '71c1cc54f91641828805369f1948d22d8389cfefc6ca2661662aa4b5c8f15a61132dddd97710433b13ed9be9656630f040a5e9782f4847471031ecc02cb9d628'
		);

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
