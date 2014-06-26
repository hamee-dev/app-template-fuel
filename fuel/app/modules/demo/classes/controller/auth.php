<?php

namespace Demo;

class Controller_Auth extends \Controller {
	const CLIENT_ID = '2zG7d5MjXPh4m8';
	const CLIENT_SECRET = 'FTNubmlpyAgE5e3BnqWt6IHJ18voxVkMS9Yh4Zjc';

	private static $client;

	public static function _init()
	{
		$user = \Session::get('account.user');
		if(is_null($user)) {
			$user = new \Model_User();
		}

		self::$client = new \Nextengine\Api\Client($user);
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
		\Session::delete('account');
		// TODO: どこかにリダイレクト
	}

	/**
	 * route: /demo/auth/callback
	 */
	public function action_callback()
	{
		if(!is_null(\Session::get('account.company')) && !is_null(\Session::get('account.user'))) {
			// TODO: 目的の画面へリダイレクト
		}

		// なぜ２個API叩くのか補足
		// 1-1. アクセストークン・リフレッシュトークンを取得
		$user_info = self::$client->apiExecute('/api_v1_login_user/info');
		$user_info = $user_info['data'][0];

		// 1-2. 取得したアクセストークンを利用して企業情報を取得
		$company_info = self::$client->apiExecute('/api_v1_login_company/info');
		$company_info = $company_info['data'][0];

		// 2-1. ログインされた企業がDBに存在しなければ、企業情報をDBに格納
		$company = new \Model_Company();

		$company->platform_id      = $company_info['company_ne_id'];
		$company->main_function_id = $company_info['company_id'];
		$company->save(true);

		// NOTE: 企業データが既に挿入済みで挿入に失敗しても、
		//       $companyのidがないとfキー制約で$userが保存できなくなるので無理やり復帰させる
		if(is_null($company->id)) {
			$company = \Model_Company::findBy('main_function_id', $company->main_function_id);
			$company = $company[0];
		}

		// 2-2. ログインされたユーザがDBに存在しなければ、ユーザ情報をDBに格納
		$user = new \Model_User();

		$user->company_id     = $company->id;
		$user->uid            = $user_info['uid'];
		$user->next_engine_id = $user_info['pic_ne_id'];
		$user->email          = $user_info['pic_mail_address'];
		$user->access_token   = self::$client->_access_token;
		$user->refresh_token  = self::$client->_refresh_token;
		$user->created_at     = \DB::expr('NOW()');
		$user->save(true);

		// 3-1. セッションにログインユーザの情報をセット
		\Session::set('account.company', $company);
		\Session::set('account.user', $user);

		echo "<a href='".\Uri::create('/demo/api/find')."'>APIのデモを見る</a>";
	}
}
