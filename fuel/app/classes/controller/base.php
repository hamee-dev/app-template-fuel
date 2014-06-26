<?php

abstract class Controller_Base extends Controller_Template
{
	/**
	 * アクセスされたユーザの言語ロケールを取得する
	 * 複数指定されている場合は、もっとも先頭に指定されている言語を返却します。  
	 * 先頭の２文字だけを見るので、３文字以上の言語略称が現れた場合にはバグります。直して下さい。
	 * 
	 * ### example
	 * - 'en' => 'en'
	 * - 'ja;q=0.6' => 'ja'
	 * - 'xxx' => 'xx'
	 * 
	 * @return string
	 */
	private static function getLocale() {
		$locales = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		return Str::sub(trim($locales[0]), 0, 2);
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
