<?php

namespace Automation;

class Service extends Automation
{
	/**
	 * loaded instance
	 */
	protected static $_instance = null;

	/**
	 * array of loaded instances
	 */
	protected static $_instances = array();

	/**
	 * Default config
	 * @var array
	 */
	protected static $_defaults = array();

	/**
	 * Init
	 */
	public static function _init()
	{
		\Config::load('automation', true);
	}

	/**
	 * Automation driver forge.
	 *
	 * @param	string			$instance		Instance name
	 * @param	array			$config		Extra config array
	 * @return  Automation instance
	 */
	public static function forge($instance = 'default', $config = array())
	{
		is_array($config) or $config = array('driver' => $config);

		$config = \Arr::merge(static::$_defaults, \Config::get('automation', array()), $config);

		$class = '\Automation\Automation_' . ucfirst(strtolower($config['driver']));

		if( ! class_exists($class, true))
		{
			throw new \FuelException('Could not find Automation driver: ' . ucfirst(strtolower($config['driver'])));
		}

		$driver = new $class($config);

		static::$_instances[$instance] = $driver;

		return $driver;
	}

	/**
	 * Return a specific driver, or the default instance (is created if necessary)
	 *
	 * @param   string  $instance
	 * @return  Automation instance
	 */
	public static function instance($instance = null)
	{
		if ($instance !== null)
		{
			if ( ! array_key_exists($instance, static::$_instances))
			{
				return false;
			}

			return static::$_instances[$instance];
		}

		if (static::$_instance === null)
		{
			static::$_instance = static::forge();
		}

		return static::$_instance;
	}
}
