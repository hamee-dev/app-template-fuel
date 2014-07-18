<?php

/**
 * Nextengine_Api_Clientのテスト
 */
class Test_Nextengine_Api_Client extends Test_Model_Base
{
	protected function getMethod($class, $method) {
		$ref = new ReflectionClass($class);

		$method = $ref->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}
	protected function getProperty($class, $prop) {
		$ref = new ReflectionClass($class);

		$prop = $ref->getProperty($prop);
		$prop->setAccessible(true);

		return $prop;
	}

	function test_init_が呼ばれるとnextengineの設定が使用できる() {
		Nextengine\Api\Client::_init();

		$this->assertTrue(is_array(Config::get('nextengine')));
	}

	function test_forge_戻り値はNextengine_Api_Clientのインスタンス() {
		$instance = Nextengine\Api\Client::forge();
		$this->assertInstanceOf('Nextengine\Api\Client', $instance);
	}

	function test_construct_引数にクライアントID_シークレット_リダイレクトURIを渡すとプロパティに反映されている() {
		$arg = array(
			'client_id'     => 'aaa',
			'client_secret' => 'bbb',
			'redirect_uri'  => 'ccc',
		);

		$ref = new ReflectionClass('Nextengine\Api\Client');
		$client = $ref->newInstanceArgs(array($arg));

		$_client_id     = $this->getProperty('Nextengine\Api\Client', '_client_id');
		$_client_secret = $this->getProperty('Nextengine\Api\Client', '_client_secret');
		$_redirect_uri  = $this->getProperty('Nextengine\Api\Client', '_redirect_uri');

		$this->assertEquals('aaa', $_client_id->getValue($client));
		$this->assertEquals('bbb', $_client_secret->getValue($client));
		$this->assertEquals('ccc', $_redirect_uri->getValue($client));
	}

	function test_setUser_クライアントのアクセストークンと渡したユーザのアクセストークンが違ったら_クライアント側のアクセストークンで上書きされる() {
		$mockUser = $this->getMock('Model_User', array('save'));
		$mockUser->expects($this->once())
				->method('save');

		$client = Nextengine\Api\Client::forge();
		$client->_access_token = 'hoge';
		$client->_refresh_token = 'foo';

		$client->setUser($mockUser);
		$this->assertEquals($client->_access_token, $mockUser->access_token);
		$this->assertEquals($client->_refresh_token, $mockUser->refresh_token);
	}
	function test_setUser_まだAPIが呼ばれていない状態でアクセストークンのあるユーザが渡されたら_ユーザモデル側のアクセストークンで上書きされる() {
		$mockUser = $this->getMock('Model_User', array('save'));
		$mockUser->expects($this->never())
				->method('save');

		$mockUser->access_token  = 'aaa';
		$mockUser->refresh_token = 'bbb';

		$client = Nextengine\Api\Client::forge();
		$client->setUser($mockUser);

		$this->assertEquals($mockUser->access_token, $client->_access_token);
		$this->assertEquals($mockUser->refresh_token, $client->_refresh_token);
	}

	// FIXME: 親クラスのapiExecuteを書き換えてレスポンスを上書きする方法が分からない
	// function test_apiExecute_APIからのレスポンスのresultフィールドがsuccessじゃなかったら例外を投げる() {
	// 	$dummy_response = array(
	// 		'result' => 'error'
	// 	);
	// }

	function test_failover_呼び出されるとそのコード_メッセージでNextengineApiExceptionを投げる() {
		$failover = $this->getMethod('Nextengine\Api\Client', 'failover');
		$this->setExpectedException('Nextengine\Api\NextengineApiException', 'hoge', '0123');

		$failover->invokeArgs(Nextengine\Api\Client::forge(), array('0123', 'hoge'));
	}

}
