<?php

namespace Nextengine\Api;

class Client_Router extends Client
{
	/**
	 * ユーザのuidを用いて認証を行う。
	 * DB < APIの順でコストが高いので、まずDBを見てから、仕方ない場合のみAPIを叩く形にしている。
	 * FIXME: 戻り値で配列を使用しているのでややオレオレ仕様になっている。
	 * 
	 * ```php
	 * list($company, $user) = $client->authenticate($uid);
	 * ```
	 * 
	 * @param  string $uid ユーザのuid
	 * @return array [Model_Company, Model_User]のインスタンスの配列。listで受け取る想定。
	 */
	public function authenticate($uid) {
		// APIを１回は呼ばないといけない
		$company = $this->_createCompany();
		$users = \Model_User::findBy('uid', $this->_uid);

		// uidに対応するユーザがいる場合、そのまま利用する
		if(count($users) > 0) {
			$user    = $users[0];

		// uidに対応するユーザが居ない場合、APIを叩き認証を行う
		} else {
			// NOTE: fキー制約の都合でcompanyが先、elseと並び順が違うのは意図的。
			$user    = $this->_createUser($company->id);
		}

		$this->setUser($user);

		// apiを1度叩かないと最新のアクセストークンが取得できないので最低１回は叩かないといけない
		if(count($users) > 0) {
			$this->apiExecute('/api_v1_login_user/info');
		}
		return array($company, $user);
	}

	/**
	 * 例外の振り分けを行う
	 */
	protected function failover($code, $message) {
		// 親クラスが例外を投げてくれるのでその詳細を解析する
		try {
			parent::failover($code, $message);
		} catch(NextengineApiException $e) {
			// NOTE: redirectはexitを打つのでbreakを書かないでも動作するが、
			//       redirectじゃない処理に変えた時に意図しないバグを防ぐためbreakを入れている。
			switch($e->getCode()) {
				// メンテナンス中 => メンテナンス中です画面へリダイレクト
				case '002007':	// 現在ネクストエンジンサーバーがメンテナンス中の為、再度時間を置いてからアクセスして下さい。
				case '003004':	// 現在メイン機能サーバーがメンテナンス中の為、再度時間を置いてからアクセスして下さい。
					\Response::redirect('/error/maintenance');
					break;

				// 混み合ってます、受注取り込み中です => しばらく待ってアクセスして下さい画面へリダイレクト
				case '003001':	// 現在メイン機能サーバーが混み合っておりますので、再度時間を置いてからアクセスして下さい。
				case '003002':	// 現在メイン機能サーバーが混み合っておりますので、再度時間を置いてからアクセスして下さい。
				case '008003':	// 受注取込中のため、更新出来ません。
				case '008007':	// 納品書印刷中の伝票があります。時間を空けて再度APIを実行して下さい。
				case '008010':	// 棚卸中のため、更新出来ません。
					\Response::redirect('/error/congestion');
					break;

				// 支払い灯の理由で利用停止、システムエラー => 営業に問い合わせてねエラー画面へリダイレクト
				case '001007':	// [xxxxx]様のネクストエンジンが、次の理由により利用停止になっています。[xxxxx]
				case '002003':	// [xxxxx]様のネクストエンジンが、次の理由により利用停止になっています。[xxxxx]
				case '003003':	// [xxxxx]様のメイン機能が、利用停止です。
				case '999999':	// APIサーバーのシステムエラーが発生しました。
					\Response::redirect('/error');
					break;

				default:
					throw $e;
			}
		}
	}

	// APIから企業情報を取得し、
	// INSERT IGNORE
	/**
	 * APIから情報を取得し企業データをDBに挿入する
	 * 既にDBに情報が存在している場合はUPDATEがかかる。
	 * FIXME: Model_BaseでINSERTorUPDATEを未実装のため、
	 *        INSERT or UPDATEではなく、INSERT IGNOREとSELECT + UPDATEを利用している。とても冗長。
	 * @return Model_Company 挿入(orDBから取得)したインスタンス
	 */
	private function _createCompany() {
		$company_info = parent::apiExecute('/api_v1_login_company/info');
		$company_info = $company_info['data'][0];

		// NOTE: http://ne0.next-engine.org:10080/ld3sl/issues/6329
		//       SELECTを打ってからINSERTをするよりも、INSERT IGNOREを使用したほうが早い
		//       毎回必ずSELECTが走るよりも、挿入に失敗したらSELECTが走る方がコストが安くなると判断したため
		//       まず一度挿入を試みて、失敗したら復帰処理としてUPDATEをかけている。
		$company = new \Model_Company();
		$company->platform_id      = $company_info['company_ne_id'];
		$company->main_function_id = $company_info['company_id'];

		// NOTE: 企業データが既に挿入済みのために挿入が発生しなくても、
		//       $companyのidがないと、fキー制約で$userが保存できなくなるので、無理やり復帰させる
		if(!$company->save(true)) {
			$company = \Model_Company::findBy('main_function_id', $company->main_function_id);
			$company = $company[0];
		}

		return $company;
	}

	/**
	 * APIから情報を取得しユーザデータをDBに挿入する
	 * 既にDBに情報が存在している場合はUPDATEがかかる。
	 * FIXME: Model_BaseでINSERTorUPDATEを未実装のため、
	 *        INSERT or UPDATEではなく、INSERT IGNOREとSELECT + UPDATEを利用している。とても冗長。
	 * @param  int $company_id
	 * @return Model_User 挿入(orDBから取得)したインスタンス
	 */
	private function _createUser($company_id) {
		$user_info = parent::apiExecute('/api_v1_login_user/info');
		$user_info = $user_info['data'][0];

		// NOTE: http://ne0.next-engine.org:10080/ld3sl/issues/6329
		//       SELECTを打ってからINSERTをするよりも、INSERT IGNOREを使用したほうが早い
		//       毎回必ずSELECTが走るよりも、挿入に失敗したらSELECTが走る方がコストが安くなると判断したため
		//       まず一度挿入を試みて、失敗したら復帰処理としてUPDATEをかけている。
		$user = new \Model_User();
		$user->company_id     = $company_id;
		$user->uid            = $user_info['uid'];
		$user->next_engine_id = $user_info['pic_ne_id'];
		$user->email          = $user_info['pic_mail_address'];
		$user->access_token   = $this->_access_token;
		$user->refresh_token  = $this->_refresh_token;
		$user->created_at     = \DB::expr('NOW()');

		// NOTE: ユーザデータが既に挿入済みのために挿入が発生しなくても、
		//       $userとDBの値が同じになっていないと、APIを叩く->ユーザモデルを更新の方式が取れないので、無理やり復帰させる
		if(!$user->save(true)) {
			$users = \Model_User::findBy('uid', $user_info['uid']);
			$user = $users[0];

			$user->access_token   = $this->_access_token;
			$user->refresh_token  = $this->_refresh_token;
			$user->save();
		}

		return $user;
	}
}
