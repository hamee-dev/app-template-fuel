<?php

class Controller_Auth extends Controller_Base {
	private static $client;

	public static function _init()
	{
		parent::_init();

		// NOTE: 認証画面ではコンストラクタに何も渡せない（login時には何も渡せるものがないので、ナシで統一）
		self::$client = new Nextengine\Api\Client_Router();
	}

	/**
	 * route: /auth/login
	 * 
	 * ネクストエンジンAPIクライアントクラスの`neLogin`メソッドを呼び出す。  
	 * neLoginの認証処理につきましては、SDKのドキュメントを御覧下さい。
	 * http://api.next-e.jp/sdk.php#php
	 */
	public function action_login()
	{
		self::$client->neLogin();
	}

	/**
	 * route: /auth/logout
	 */
	public function action_logout()
	{
		Session::destroy();
		Response::redirect('/');
	}

	/**
	 * route: /auth/callback
	 * ネクストエンジンAPIの認証が済むとリダイレクトされるメソッドです。
	 * セッションやGETパラメータの値を見て、認証済みのデータをDBとセッションに保存します。
	 */
	public function action_callback()
	{
		// NOTE: 可読性と関数呼び出しのオーバーヘッド軽減のため、結果を変数にキャッシュ
		$session_user = Session::get('account.user');
		$get_uid      = Input::get('uid');
		$get_state    = Input::get('state');

		// セッションもURLにも何もない = 通常操作では起こりえない非正規ルートなので再認証させる
		if(is_null($get_uid) && is_null($get_state) && is_null($session_user)) {
			Response::redirect('/demo/auth/login');
		}

		// セッションがある = 既にログイン済みなのでセッションのuidを使って認証
		// URLに何がついてようといなかろうと、セッションの値を使っている。
		if(!is_null($session_user)) {
			list($company, $user) = self::$client->authenticate($session_user->uid);
		} else {
			// セッションがなくURLにuidとstateが渡っていたら、URLのuidを使って認証
			// NOTE: GETがない場合は弾いているので、GETパラメータがある前提でOK
			list($company, $user) = self::$client->authenticate($get_uid);
		}

		// セッションにログインユーザの情報をセット
		$company_key = Config::get('session.keys.ACCOUNT_COMPANY');
		$user_key = Config::get('session.keys.ACCOUNT_USER');
		Session::set($company_key, $company);
		Session::set($user_key, $user);

		// NOTE: 動作デモを試したら、コメントアウトを解除して任意の場所へリダイレクトさせて下さい。
		//       http://api.next-e.jp/secret/sample-fuelphp/about-sample.php
		// Response::redirect('/demo/api/find');

		// NOTE: 上記を編集しリダイレクトさせる場合には下記の記述は不要です。
		$this->template->title = 'Authenticate complete!!';
		$this->template->content = "";
	}
}
