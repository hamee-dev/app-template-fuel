<?php

return array(
	/**
	 * Default settings
	 */
	'defaults' => array(

		'useragent'	=> 'FuelPHP, PHP 5.3 Framework',
		'is_html'		=> null,
		'charset'		=> 'utf-8',
		'newline'	=> "\n",

		/**
		 * Ecoding (8bit, base64 or quoted-printable)
		 */
		'encoding'		=> '8bit',

		/**
		 * Wordwrap size, set to null, 0 or false to disable wordwrapping
		 */
		'wordwrap'	=> null,

		/**
		 * Default sender details
		 */
		'from'		=> array(
			'email'		=> 'YOUR_EMAIL@ADDRESS.COM',
			'name'		=> 'ADMIN',
		),

		/**
		 * Mail driver (mail, smtp, sendmail, noop)
		 */
		'driver'		=> 'mail',

		/**
		 * Wether to encode subject and recipient names.
		 * Requires the mbstring extension: http://www.php.net/manual/en/ref.mbstring.php
		 */
		'encode_headers' => true,

		/**
		 * Email priority
		 */
		'priority'		=> \Email::P_NORMAL,

		/**
		 * Whether to validate email addresses
		 */
		'validate'	=> true,

		/**
		 * Path to sendmail
		 */
		'sendmail_path' => '/usr/sbin/sendmail',

		/**
		 * SMTP settings
		 */
		'smtp'	=> array(
			'host'		=> '',
			'port'		=> 25,
			'username'	=> '',
			'password'	=> '',
			'timeout'	=> 5,
		),

		/**
		 * Attachment paths
		 */
		'attach_paths' => array(
			// absolute path
			'',
			// relative to docroot.
			DOCROOT,
		),
	),

	/**
	 * Default setup group
	 */
	'default_setup' => 'default',

	/**
	 * Setup groups
	 */
	'setups' => array(
		'default' => array(),
	),

);
