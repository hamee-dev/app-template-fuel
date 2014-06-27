<?php

class Controller_Auth extends \Controller {
	const CLIENT_ID = '2zG7d5MjXPh4m8';
	const CLIENT_SECRET = 'FTNubmlpyAgE5e3BnqWt6IHJ18voxVkMS9Yh4Zjc';

	private static $client;

	public static function _init()
	{
		// NOTE: 認証画面ではコンストラクタに何も渡せない（login時には何も渡せるものがないので、ナシで統一）
		self::$client = new Nextengine\Api\Client_Router();
	}

	/**
	 * route: /demo/auth/login
	 */
	public function action_login()
	{
		self::$client->neLogin();
	}

	/**
	 * route: /demo/auth/logout
	 */
	public function action_logout()
	{
		Session::delete('account');
		// TODO: どこかにリダイレクト
	}

	/**
	 * route: /demo/auth/callback
	 */
	public function action_callback()
	{
		// NOTE: 可読性と関数呼び出しのオーバーヘッド軽減のため、結果を変数にキャッシュ
		$session_user = Session::get('account.user');
		$get_uid      = Input::get('uid');
		$get_state    = Input::get('state');

		// セッションもURLにも何もない = 通常操作では起こりえない非正規ルートなので再認証させる
		if(is_null($get_uid) && is_null($get_state)) {
			Response::redirect('/demo/auth/login');
		}

		// セッションがある = 既にログイン済みなのでセッションのuidを使って認証
		// URLに何がついてようといなかろうと、セッションの値を使っている。
		if(!is_null($session_user)) {
			list($company, $user) = self::$client->authenticate($session_user->uid);
		} else {
			// セッションがなくURLにuidとstateが渡っていたら、URLのuidを使って認証
			// NOTE: GETがない場合は弾いているのである前提でOK
			list($company, $user) = self::$client->authenticate($get_uid);
		}

		// セッションにログインユーザの情報をセット
		Session::set('account.company', $company);
		Session::set('account.user', $user);

		echo "<a href='".\Uri::create('/demo/api/find')."'>APIのデモを見る</a>";
	}
}
