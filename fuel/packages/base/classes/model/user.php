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
	 * ネクストエンジンAPIのアクセストークンの有効期限
	 * @var string
	 */
	public $access_token_end_date  = null;

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

	/**
	 * アクセストークンを更新する
	 *
	 * access_token_end_dateを用いて大小比較する。
	 * access_token_end_dateが古い場合、更新をしない。新しい場合、更新する
	 *
	 * @param string access_token アクセストークン
	 * @param string refresh_token リフレッシュトークン
	 * @param string access_token_end_date アクセストークン有効期限日時
	 * @return int 作用行数
	 */
	public function updateCredentials($access_token, $refresh_token, $access_token_end_date)
	{
		return \DB::update(self::_getTableName())
			->set([
				'access_token'  => $access_token,
				'refresh_token' => $refresh_token,
				'access_token_end_date' => $access_token_end_date
			])
			->where('id', '=', $this->id)
			->where('access_token_end_date', '<', $access_token_end_date)
			->execute();
	}
}
