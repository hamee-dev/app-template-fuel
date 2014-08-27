<?php

require_once __DIR__.'/../client.php';

/**
 * Nextengine_Api_Client_Routerのテスト
 */
class Test_Nextengine_Api_Client_Router extends Test_Common
{
	private function getClient() {
		$user = Model_User::find(1);
		$client = new Nextengine\Api\Client_Router();
		$client->setUser($user);

		return $client;
	}
	function test_authenticate_createCompanyが呼ばれる() {}
	function test_authenticate_uidに対応するユーザがいる場合_ユーザ取得用のAPIは叩かれない() {}
	function test_authenticate_uidに対応するユーザがいない場合_ユーザ取得用のAPIが叩かれる() {}
	function test_authenticate_戻り値は配列で_企業_ユーザの順の配列で帰ってくる() {}

	// FIXME: リダイレクトが走るとexitされてしまうのでテストできない
	// function test_failover_コードが002007_003004（メンテナンス中系）の場合はメンテナンス画面へリダイレクトする() {}
	// function test_failover_コードが003001_003002_008003_008007_008010（待ち状態）の場合はしばらくお待ち下さい画面へリダイレクトする() {}
	// function test_failover_コードが001007_002003_003003_999999（支払停止やシステムエラー系）の場合は営業にお問い合わせ下さい画面へリダイレクトする() {}
	function test_failover_リダイレクトされないコードの場合はNextengineApiExceptionがスローされる() {
		$not_matched_code = '123456';
		$failover = $this->getMethod('Nextengine\Api\Client_Router', 'failover');

		$this->setExpectedException('Nextengine\Api\NextengineApiException');

		$client = new Nextengine\Api\Client_Router();
		$failover->invokeArgs($client, array($not_matched_code, 'hogehoge'));
	}
}
