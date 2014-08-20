<?php


require_once __DIR__.'/../common.php';

/**
 * Controller_Authのテスト
 */
class Test_Controller_Auth extends Test_Common
{
	// 言語の設定をリセット
	public function setUp() {
		parent::setUp();

		// CLIだとundefined indexと言われるので明示的にnullを設定
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = null;

		Config::set('language', null);
	}

	private function getClient() {
		return parent::getProperty('Controller_Auth', 'client');
	}

	function test__init_を呼ぶとclientにはクライアントのインスタンスがセットされている() {
		$client = $this->getClient();
		$this->assertInstanceOf('\NextEngine\Api\Client', $client->getValue());
	}
}
