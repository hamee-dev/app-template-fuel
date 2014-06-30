<?php

/**
 * ネクストエンジンAPIを利用する際に継承する抽象クラス
 * APIを使用する画面で共通処理として必要なセッション処理、クライアントの初期化を行う。
 * 
 * NOTE: 継承クラスで別途_initの処理を書きたくなった際には、「必ず」parent::_initをコールして下さい。
 *       こいつを呼んでもらえないと言語ファイルのロードが出来ません。
 */
abstract class Controller_Neapi extends Controller_Base {
	/**
	 * ネクストエンジンAPIクライアントのインスタンスを格納する
	 * @var \Nextengine\Api\Client
	 */
	protected static $client;

	/**
	 * APIを使用する画面で共通処理として必要なセッション処理、クライアントの初期化を行う。
	 * @return void
	 */
	public static function _init()
	{
		parent::_init();

		$user_key = Config::get('session.keys.ACCOUNT_USER');
		$session_user = \Session::get($user_key);

		// セッション切れならログイン処理へリダイレクト
		if(is_null($session_user)) {
			Response::redirect('/auth/login');
		}

		$user = Model_User::find($session_user->id);
		self::$client = new Nextengine\Api\Client_Router();
		self::$client->setUser($user);
	}
}
