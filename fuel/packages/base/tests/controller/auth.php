<?php

require_once __DIR__.'/../common.php';

// \Base\Controller_Authはabstractなのでインスタンス化できるよう継承
$_SERVER['HTTP_ACCEPT_LANGUAGE'] = null;
class Dummy_Controller_Test extends \Base\Controller_Auth {}

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

	function test__create_company_戻り値はCompanyモデルのインスタンス() {
		$create_company = $this->getMethod('Dummy_Controller_Test', '_create_company');

		$request  = Request::forge('/hoge/foo');
		$instance = new Dummy_Controller_Test($request);
		$info     = array(
			'company_ne_id' => 'xxxxxxxxxx',
			'company_id' => 'xxxxxxxxxx',
		);

		$result = $create_company->invokeArgs($instance, array($info));
		$this->assertInstanceOf('\Model_Company', $result);
	}

	function test__create_user_戻り値はUserモデルのインスタンス() {
		$create_user = $this->getMethod('Dummy_Controller_Test', '_create_user');

		$request  = Request::forge('/hoge/foo');
		$instance = new Dummy_Controller_Test($request);
		$info     = array(
			'uid' => 'xxxxxxxxxx',
			'pic_ne_id' => 'xxxxxxxxxx',
			'pic_mail_address' => 'xxxxxxxxxx',
		);

		$result = $create_user->invokeArgs($instance, array($info, 1));
		$this->assertInstanceOf('\Model_User', $result);
	}
}
