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
	* 設定値を格納する
	* @var array
	*/
	protected $config = array();

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
	 * @param  array $config Config array
	 * @return Nextengine
	 */
	public static function forge($config = array())
	{
		$config = \Arr::merge(static::$_defaults, \Config::get('nextengine', array()), $config);

		$class = new static($config);

		return $class;
	}

	/**
	 * コンストラクタに与えられた設定でデフォルト設定を上書きし、接続に必要な情報を格納する
	 * @param array $config driver config
	 */
	public function __construct(array $config = array())
	{
		$config = \Arr::merge($config, self::$_defaults);

		$this->_client_id     = $config['client_id'];
		$this->_client_secret = $config['client_secret'];
		$this->_redirect_uri  = $config['redirect_uri'];

		parent::__construct($this->_client_id, $this->_client_secret, $this->_redirect_uri);
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
		$response = parent::apiExecute($path, $api_params, $redirect_uri);

		// TODO: resultがsuccessじゃなかったらエラーコードによって例外を投げる
		if($response['result'] !== self::RESULT_SUCCESS) {
			$this->failover($response['code'], $response['message']);
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
