<?php

class Controller_Neapi extends Controller_Base {
	protected static $client;

	public static function _init()
	{
		parent::_init();
		$session_user = \Session::get('account.user');

		// セッション切れ
		if(is_null($session_user)) {
			Response::redirect('/auth/login');
		}

		$user = Model_User::find($session_user->id);
		self::$client = new Nextengine\Api\Client_Router();
		self::$client->setUser($user);
	}
}