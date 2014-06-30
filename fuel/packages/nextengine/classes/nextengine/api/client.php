<?php

namespace Nextengine\Api;

require_once __DIR__.DS.'..'.DS.'..'.DS.'neApiClient.php';

class NextengineApiException extends \FuelException {
	public function __construct($message, $code) {
		$this->code = $code;
		parent::__construct($message, 0);
	}
}

class Client extends \neApiClient
{
	const RESULT_SUCCESS  = 'success';
	const RESULT_ERROR    = 'error';
	const RESULT_REDIRECT = 'redirect';

	/**
	 * デフォルトの設定値
	 * @var array
	 */
	protected static $_defaults = array();

	/**
	 * ユーザオブジェクトを格納する
	 * @var array
	 */
	protected $user = null;

	/**
	 * 設定をロードし格納しておく
	 */
	public static function _init()
	{
		self::$_defaults = \Config::load('nextengine', true);
	}

	/**
	 * FuelPHP式（ファクトリメソッド）のコンストラクタ
	 * 内部は単にコンストラクタを呼ぶだけ
	 *
	 * @param  array      $config 設定値（この値が優先される）
	 * @return Nextengine\Api\Client
	 */
	public static function forge(array $config = array())
	{
		$class = new static($config);
		return $class;
	}

	/**
	 * コンストラクタに与えられた設定でデフォルト設定を上書きし、接続に必要な情報を格納する
	 * @param  array      $config 設定値（この値が優先される）
	 */
	public function __construct(array $config = array())
	{
		$config = \Arr::merge(self::$_defaults, $config);

		parent::__construct(
			$config['client_id'],
			$config['client_secret'],
			$config['redirect_uri']);
	}

	/**
	 * ユーザ情報をセットする
	 * @param Model_User $user ユーザのインスタンス
	 * @return void
	 */
	public function setUser($user) {
		// クライアントのアクセストークンがNULL以外で、DBの値と違っていたら、DBを更新
		// NOTE: クライアントのプロパティが最も最新、DBの値はその次に新しい。優先すべきはクライアントのプロパティ。
		if(!is_null($this->_access_token) && $user->access_token !== $this->_access_token) {
			$user->access_token = $this->_access_token;
			$user->refresh_token = $this->_refresh_token;
			$user->save();
		}

		// まだAPIを叩いていない状態で、アクセストークンがあるユーザが渡されたら、クライアントのプロパティを更新
		if(is_null($this->_access_token) && !is_null($user->access_token)) {
			$this->_access_token = $user->access_token;
			$this->_refresh_token = $user->refresh_token;
		}

		$this->user = $user;
	}

	/**
	 * ネクストエンジンAPIを叩く
	 * 
	 * ### 拡張点
	 * 親クラスから拡張した処理は、
	 * レスポンスに含まれるresultフィールドの値がsuccessでないなら、例外を投げるという処理を追加した
	 *
	 * @override
	 */
	public function apiExecute($path, $api_params = array(), $redirect_uri = NULL) {
		$before_exec_access_token = $this->_access_token;
		$response = parent::apiExecute($path, $api_params, $redirect_uri);

		// TODO: resultがsuccessじゃなかったら、エラーコードによって例外を投げる
		// エラーの種類については→を参照：http://api.next-e.jp/message.php
		// エラー時の振る舞いについては、継承クラスのfailoverメソッドを参照。
		if($response['result'] !== self::RESULT_SUCCESS) {
			$this->failover($response['code'], $response['message']);
		}

		if(!is_null($this->user) && $before_exec_access_token !== $this->_access_token) {
			$this->user->access_token  = $this->_access_token;
			$this->user->refresh_token = $this->_refresh_token;
			$this->user->save();

			$user_key = Config::get('session.keys.ACCOUNT_USER');
			Session::set($user_key, $this->user);
		}

		return $response;
	}

	/**
	 * ネクストエンジンAPIからエラーが帰っていた場合の処理を行う
	 */
	protected function failover($code, $message) {
		// TODO: ログの出力/メール送信
		throw new NextengineApiException($message, $code);
	}
}
