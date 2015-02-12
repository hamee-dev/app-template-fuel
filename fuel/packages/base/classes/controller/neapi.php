<?php

namespace Base;

/**
 * ネクストエンジンAPIを使用するコントローラの基底となるクラス
 * 
 * ネクストエンジンAPIを利用する際に継承する抽象クラス
 * APIを使用する画面で共通処理として必要なセッション処理、クライアントの初期化を行う。
 * 
 * NOTE: 継承クラスで別途_initの処理を書きたくなった際には、「必ず」parent::_initをコールして下さい。
 * parent::_initをコールしないと言語ファイルのロードなど必要な初期化処理が行われません
 */
abstract class Controller_Neapi extends Controller_Base
{
	/**
	 * ネクストエンジンAPIクライアントのインスタンスを格納する
	 * @var \Nextengine\Api\Client
	 */
	protected static $client;

	/**
	 * APIを使用する画面で共通処理として必要な処理、クライアントの初期化を行う。
	 * 
	 * @return void
	 */
	public function before()
	{
		parent::before();

		if(is_null($this->user)) {
			\Response::redirect('/auth/login');
		}

		self::$client = new \Nextengine\Api\Client_Router();
		self::$client->setUser($this->user);
	}
}
