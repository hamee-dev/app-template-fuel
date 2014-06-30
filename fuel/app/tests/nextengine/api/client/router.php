<?php

/**
 * Nextengine_Api_Client_Routerのテスト
 */
class Test_Nextengine_Api_Client_Router extends Test_Nextengine_Api_Client
{
	private function getClient() {
		$user = Model_User::find(1);
		$client = new Nextengine\Api\Client_Router();
		$client->setUser($user);

		return $client;
	}
	function test_authenticate_createCompanyが呼ばれる() {}
	function test_authenticate_uidに対応するユーザがいる場合、ユーザ取得用のAPIは叩かれない() {}
	function test_authenticate_uidに対応するユーザがいない場合、ユーザ取得用のAPIが叩かれる() {}
	function test_authenticate_戻り値は配列で、企業、ユーザの順の配列で帰ってくる() {}

	// FIXME: リダイレクトが走るとexitされてしまうのでテストできない
	// function test_failover_コードが002007_003004（メンテナンス中系）の場合はメンテナンス画面へリダイレクトする() {}
	// function test_failover_コードが003001_003002_008003_008007_008010（待ち状態）の場合はしばらくお待ち下さい画面へリダイレクトする() {}
	// function test_failover_コードが001007_002003_003003_999999（支払停止やシステムエラー系）の場合は営業にお問い合わせ下さい画面へリダイレクトする() {}
	function test_failover_それ以外のコードの場合はNextengineApiExceptionがスローされる() {
		$not_matched_code = '123456';
		$failover = $this->getMethod('Nextengine\Api\Client_Router', 'failover');

		$this->setExpectedException('Nextengine\Api\NextengineApiException');

		$client = new Nextengine\Api\Client_Router();
		$failover->invokeArgs($client, array($not_matched_code, 'hogehoge'));
	}

	function test__createCompany_戻り値はModel_Companyのインスタンス() {
		$_createCompany = $this->getMethod('Nextengine\Api\Client_Router', '_createCompany');
		$client = $this->getClient();

		$company = $_createCompany->invoke($client);
		$this->assertInstanceOf('Model_Company', $company);
	}
	function test__createCompany_企業データがDBにある場合はDBに挿入されない() {
		$_createCompany = $this->getMethod('Nextengine\Api\Client_Router', '_createCompany');
		$client = $this->getClient();

		$before_called_rows = Model_Company::count();
		$company = $_createCompany->invoke($client);
		$after_called_rows = Model_Company::count();

		$this->assertEquals($before_called_rows, $after_called_rows);
	}
	function test__createCompany_企業データがDBにない場合はDBに挿入される() {
		$_createCompany = $this->getMethod('Nextengine\Api\Client_Router', '_createCompany');
		$client = $this->getClient();

		// Fキー制約があるのでユーザも削除
		$company = Model_Company::find(1);
		$referenced_users = Model_User::findBy('company_id', $company->id);
		foreach($referenced_users as $user) {
			$user->delete();
		}
		$company->delete();

		$before_called_rows = Model_Company::count();
		$company = $_createCompany->invoke($client);
		$after_called_rows = Model_Company::count();

		$this->assertEquals($before_called_rows + 1, $after_called_rows);
	}

	function test__createUser_戻り値はModel_Userのインスタンス() {
		$_createUser    = $this->getMethod('Nextengine\Api\Client_Router', '_createUser');
		$client    = $this->getClient();
		$companies = Model_Company::findAll();
		$company   = $companies[0];

		$user = $_createUser->invokeArgs($client, array($company->id));
		$this->assertInstanceOf('Model_User', $user);
	}
	function test__createUser_存在しないcompany_idのデータは作成できない※Fキー制約が効いている() {
		$_createUser    = $this->getMethod('Nextengine\Api\Client_Router', '_createUser');
		$client    = $this->getClient();

		$not_exist_company_id = 100000;

		$before_called_rows = Model_User::count();
		$company = $_createUser->invokeArgs($client, array($not_exist_company_id));
		$after_called_rows = Model_User::count();

		$this->assertEquals($before_called_rows, $after_called_rows);
	}

}
