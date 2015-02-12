<?php

namespace Base;

/**
 * ユーザ情報を保持するモデル
 */
class Model_User extends Model_Base
{
	/**
	 * 企業IDを保持するカラム
	 * @var string
	 */
	public $company_id;

	/**
	 * ネクストエンジンAPIから取得したUIDを保持するカラム
	 * @see http://api.next-e.jp/fields_login.php#pic ネクストエンジンAPI
	 * @var string
	 */
	public $uid;

	/**
	 * メールアドレスを保持するカラム
	 * @var string
	 */
	public $email;

	/**
	 * ネクストエンジン担当者IDを保持するカラム
	 * @var string
	 */
	public $next_engine_id;

	/**
	 * ネクストエンジンAPIのアクセストークンを保持するカラム
	 * @var string
	 */
	public $access_token  = null;

	/**
	 * ネクストエンジンAPIのリフレッシュトークンを保持するカラム
	 * @var string
	 */
	public $refresh_token = null;
}
