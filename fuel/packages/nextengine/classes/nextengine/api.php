<?php

namespace Nextengine;

require_once __DIR__.DS.'..'.DS.'neApiClient.php';

class NextengineApiException extends \FuelException {}

class Nextengine_Api extends \neApiClient
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
		\Config::load('nextengine', true);
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
		$this->config = $config;
	}

	/**
	* Get a config setting.
	*
	* @param string $key the config key
	* @param mixed  $default the default value
	* @return mixed the config setting value
	*/
	public function get_config($key, $default = null)
	{
		return \Arr::get($this->config, $key, $default);
	}

	/**
	* Set a config setting.
	*
	* @param string $key the config key
	* @param mixed $value the new config value
	* @return object $this for chaining
	*/
	public function set_config($key, $value)
	{
		\Arr::set($this->config, $key, $value);

		return $this;
	}
}
