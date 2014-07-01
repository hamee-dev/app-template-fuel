<?php

require_once __DIR__.'/base.php';
require_once __DIR__.'/../usedb.php';

/**
 * Controller_Authのテスト
 */
class Test_Controller_Auth extends Usedb
{
	// 言語の設定をリセット
	public function setUp() {
		// CLIだとundefined indexと言われるので明示的にnullを設定
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = null;

		Config::set('language', null);
	}

	private function getClient() {
		$ref = new ReflectionClass('Controller_Auth');
		$client = $ref->getProperty('client');
		$client->setAccessible(true);

		return $client;
	}

	function test__init_を呼ぶとclientにはクライアントのインスタンスがセットされている() {
		$client = $this->getClient();
		$this->assertInstanceOf('\NextEngine\Api\Client', $client->getValue());
	}

	function test_action_login_クライアントのneLoginがコールされる() {
		$mock = $this->getMock('\NextEngine\Api\Client_Router', array('neLogin'));
		$mock->expects($this->once())
				->method('neLogin');

		$client = $this->getClient();
		$client->setValue($mock);

		$controller = new Controller_Auth(Request::forge());
		$controller->action_login();
	}

	// NOTE: リダイレクトの操作を入れたらリダイレクトで強制終了されてしまうのでテストしない
	// function test_action_logout_セッションが全て破棄される() {
	// 	$user_key = Config::get('session.keys.ACCOUNT_USER');
	// 	$company_key = Config::get('session.keys.ACCOUNT_COMPANY');
	// 	$controller = new Controller_Auth(Request::forge());

	// 	Session::set($user_key, 'aaa');
	// 	Session::set($company_key, 'xxx');

	// 	$controller->action_logout();
 
	// 	$this->assertEquals(null, Session::get($user_key));
	// 	$this->assertEquals(null, Session::get($company_key));
	// }

	private function getAuthenticateChecker($uid) {
		$mock = $this->getMock('\NextEngine\Api\Client_Router', array('authenticate'));
		$mock->expects($this->once())
				->method('authenticate')
				->with($this->equalTo($uid));

		return $mock;
	}

	function test_action_callback_SESSIONがなく、GETのuidとstateがあるならGETを使って認証処理を行う() {
		$user_key = Config::get('session.keys.ACCOUNT_USER');
		Session::set($user_key, null);
		// 単にGETに値を入れたいだけで、任意の文字列を入れている。
		$_GET['uid']	= 'aaaaaa';
		$_GET['state']	= 'xxxxxx';

		$mock = $this->getAuthenticateChecker(Input::get('uid'));
		$client = $this->getClient();
		$client->setValue($mock);

		$controller = new Controller_Auth(Request::forge());

		// NOTE: 既にテスト結果が出力されてしまっているのでリダイレクトしようとするとエラーになる
		//       ダミー情報をセットしているので認証するため、リダイレクトがかかる。ので発生する例外を無視する。
		try { $controller->action_callback(); } catch(\Exception $e) {}
	}
	function test_action_callback_SESSIONがある、GETはない場合はセッションを用い認証を行う() {
		$user = new Model_User(array(
			'uid' => 'hogehoge'
		));
		$user_key = Config::get('session.keys.ACCOUNT_USER');
		Session::set($user_key, $user);

		$_GET['uid']	= null;
		$_GET['state']	= null;

		$mock = $this->getAuthenticateChecker($user->uid);
		$client = $this->getClient();
		$client->setValue($mock);

		$controller = new Controller_Auth(Request::forge());

		// NOTE: 既にテスト結果が出力されてしまっているのでリダイレクトしようとするとエラーになる
		//       ダミー情報をセットしているので認証するためにリダイレクトがかかる。
		try { $controller->action_callback(); } catch(\Exception $e) {}
	}

}
