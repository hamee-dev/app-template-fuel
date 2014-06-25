<?php

namespace Nextengine\Api;

require_once __DIR__.DS.'..'.DS.'..'.DS.'neApiClient.php';

class NextengineApiException extends \FuelException {}
// class NextengineApi**Exception extends NextengineApiException {}

class Client extends \neApiClient
{
	const RESULT_SUCCESS  = 'success';
	const RESULT_ERROR    = 'error';
	const RESULT_REDIRECT = 'redirect';

	/**
	 * Default config
	 * @var array
	 */
	protected static $_defaults = array();

	/**
	* Driver config
	* @var array
	*/
	protected $config = array();

	/**
	 * Init
	 */
	public static function _init()
	{
		self::$_defaults = \Config::load('nextengine', true);
	}

	/**
	 * Nextengine driver forge.
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
	* Driver constructor
	*
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

	public function apiExecute($path, $api_params = array(), $redirect_uri = NULL) {
		$response = parent::apiExecute($path, $api_params, $redirect_uri);

		// TODO: resultがsuccessじゃなかったらエラーコードによって例外を投げる
		if($response['result'] !== self::RESULT_SUCCESS) {
			$this->failover($response['code'], $response['message']);
		}

		return $response;
	}

	public function neLogin($redirect_uri = NULL) {
		return parent::neLogin($redirect_uri);
	}

	protected function failover($code, $message) {
		// TODO: ログの出力/メール送信
		throw new NextengineApiException($code, $message);
	}
}
