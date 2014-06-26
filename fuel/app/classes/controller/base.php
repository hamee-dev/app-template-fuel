<?php

abstract class Controller_Base extends Controller_Template
{
	/**
	 * アクセスされたユーザの言語ロケールを取得する
	 * 要求度付きのロケールでも動作します。
	 * ### sample
	 * en => en
	 * ja;q=0.6 => ja
	 * @return string
	 */
	private static function getLocale() {
		$locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$locale = explode(';q=', $locales[0]);	// 要求度が付与されている場合に削除する
		return $locale[0];
	}

	/**
	 * 言語の設定、言語ファイルの読み込みを行う
	 * ここに書いておくことで継承クラスは言語ファイルのロードを考慮せずコーディングできる
	 * 
	 * NOTE: 継承クラスで別途_initの処理を書きたくなった際には、「必ず」parent::_initをコールして下さい。
	 *       こいつを呼んでもらえないと言語のロードが出来ません。
	 * @return void
	 */
	public static function _init() {
		Config::set('language', self::getLocale());

		Lang::load('common.yml', 'common');
		Lang::load('model.yml', 'model');
		Lang::load('page.yml', 'page');
		Lang::load('message.yml', 'message');
	}
}
