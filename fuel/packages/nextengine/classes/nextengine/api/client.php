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
	 * 設定をロードし格納しておく
	 */
	public static function _init()
	{
		self::$_defaults = \Config::load('nextengine', true);
	}

	/**
	 * FuelPHP式（ファクトリメソッド）のコンストラクタ
	 *
	 * @param  Model_User $user   ユーザのインスタンス
	 * @param  array      $config 設定値（この値が優先される）
	 * @return Nextengine\Api\Client
	 */
	public static function forge(\Model_User $user, $config = array())
	{
		$config = \Arr::merge(static::$_defaults, $config);

		$class = new static($config);

		return $class;
	}

	/**
	 * コンストラクタに与えられた設定でデフォルト設定を上書きし、接続に必要な情報を格納する
	 * @param  Model_User $user   ユーザのインスタンス
	 * @param  array      $config 設定値（この値が優先される）
	 */
	public function __construct(\Model_User $user, array $config = array())
	{
		$config = \Arr::merge(self::$_defaults, $config);

		$this->user = $user;

		parent::__construct(
			$config['client_id'],
			$config['client_secret'],
			$config['redirect_uri'],
			$user->access_token,
			$user->refresh_token);
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
		$current_access_token = $this->_access_token;

		$response = parent::apiExecute($path, $api_params, $redirect_uri);

		// TODO: resultがsuccessじゃなかったらエラーコードによって例外を投げる
		if($response['result'] !== self::RESULT_SUCCESS) {
			$this->failover($response['code'], $response['message']);
		}

		if($current_access_token !== $this->_access_token) {
			$this->user->access_token  = $this->_access_token;
			$this->user->refresh_token = $this->_refresh_token;
			$this->user->save();
		}

		return $response;
	}

	/**
	 * ネクストエンジンAPIにログインを行う
	 * 
	 * ### 拡張点
	 * 今のところ無いのでコメントアウト
	 * 
	 * @override
	 */
	// public function neLogin($redirect_uri = NULL) {
	// 	return parent::neLogin($redirect_uri);
	// }

	/**
	 * ネクストエンジンAPIからエラーが帰っていた場合の処理を行う
	 */
	protected function failover($code, $message) {
		// TODO: ログの出力/メール送信
		throw new NextengineApiException($message, $code);
	}
}
