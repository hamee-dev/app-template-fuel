<?php

namespace Nextengine\Api;

require_once __DIR__.DS.'..'.DS.'..'.DS.'neApiClient.php';

// class NextengineApiException extends \FuelException {}

class Client extends \neApiClient
{
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
	 * @param	array			$config		Config array
	 * @return  Nextengine
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

		$this->_client_id		= $config['client_id'];
		$this->_client_secret	= $config['client_secret'];
		$this->_redirect_uri	= $config['redirect_uri'];

		parent::__construct($this->_client_id, $this->_client_secret, $this->_redirect_uri);
	}
}
