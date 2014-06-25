<?php

namespace Demo;

class Controller_Auth extends \Controller {
	const CLIENT_ID = '2zG7d5MjXPh4m8';
	const CLIENT_SECRET = 'FTNubmlpyAgE5e3BnqWt6IHJ18voxVkMS9Yh4Zjc';

	private static $client;

	public static function _init()
	{
		// /demo/auth/callbackにリダイレクトする
		$redirect_uri = \Uri::base(false).'demo/auth/callback';
		self::$client = new \Nextengine\Api\Client();
	}

	/**
	 * route: /demo/auth/login
	 */
	public function action_login()
	{
		self::$client->neLogin();
	}

	/**
	 * route: /demo/auth/logout
	 */
	public function action_logout()
	{
		Session::delete('account');
		// TODO: どこかにリダイレクト
	}

	/**
	 * route: /demo/auth/callback
	 */
	public function action_callback()
	{
		// uidとstateがURLで渡っていたら
		if(\Input::get('uid') && \Input::get('state')) {
			// 1-1. アクセストークン・リフレッシュトークンを取得
			$auth_response = self::$client->apiExecute('/api_neauth', array(
				'uid' => \Input::get('uid'),
				'state' => \Input::get('state'),
			));
			// 1-2. 取得したアクセストークンを利用して企業情報を取得
			$company_info = self::$client->apiExecute('/api_v1_login_company/info');
			$company_info = $company_info['data'][0];


			// 2-1. ログインされた企業がDBに存在しなければ、企業情報をDBに格納
			$company = new \Model_Company();

			$company->platform_id      = $auth_response['company_ne_id'];
			$company->main_function_id = $company_info['company_id'];
			$company->save();

			// 2-2. ログインされたユーザがDBに存在しなければ、ユーザ情報をDBに格納
			$user = new \Model_User();

			$user->company_id     = $company->id;
			$user->uid            = $auth_response['uid'];
			$user->next_engine_id = $auth_response['pic_ne_id'];
			$user->email          = $auth_response['pic_mail_address'];
			$user->access_token   = $auth_response['access_token'];
			$user->refresh_token  = $auth_response['refresh_token'];
			$user->created_at     = \DB::expr('NOW()');
			$user->save();

			// 3-1. セッションにログインユーザの情報をセット
			\Session::set('account.company', $company);
			\Session::set('account.user', $user);

		// uidとstateがURLになく、セッションにも認証情報がない場合、再認証する
		} else if(is_null(\Session::get('account.company')) && is_null(\Session::get('account.user'))) {
			\Response::redirect(\Uri::create('/demo/auth/login'));
		}

		echo "<a href='".\Uri::create('/demo/api/find')."'>APIのデモを見る</a>";
	}
}
