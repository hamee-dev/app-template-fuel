<?php

namespace Base;

/**
 * 企業情報を保持するモデル
 */
class Model_Company extends Model_Base
{
	/**
	 * `メイン機能企業ID`を保持するカラム
	 * 
	 * @see http://api.next-e.jp/fields_login.php#company ネクストエンジンAPI
	 * @var string
	 */
	public $main_function_id;

	/**
	 * `ネクストエンジン企業ID`を保持するカラム
	 * 
	 * @see http://api.next-e.jp/fields_login.php#company ネクストエンジンAPI
	 * @var string
	 */
	public $platform_id;
}
