<?php

/**
 * コントローラの基底クラス。
 * 全画面で共通処理として必要な言語ファイルのロード、表示言語のセットを行う。
 * 
 * NOTE: 継承クラスで別途_initの処理を書きたくなった際には、「必ず」parent::_initをコールして下さい。
 *       こいつを呼んでもらえないと言語ファイルのロードが出来ません。
 */
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
	 * @param string $raw_locale ブラウザから送信された言語情報を表す文字列
	 * @return string
	 */
	private static function getLocale($raw_locale) {
		$locales = explode(',', $raw_locale);
		return Str::sub(trim($locales[0]), 0, 2);
	}

	/**
	 * アクセスされた情報から、表示言語をセットする
	 * 
	 * @param string $lang 'ja', 'en'などの言語を表す文字列
	 * @return void
	 */
	private static function setLanguage($lang) {
		Config::set('language', $lang);
	}

	/**
	 * 言語の設定、言語ファイルの読み込みを行う
	 * ここに書いておくことで継承クラスは言語ファイルのロードを考慮せずコーディングできる
	 * 
	 * @return void
	 */
	public static function _init() {
		self::setLanguage(self::getLocale($_SERVER['HTTP_ACCEPT_LANGUAGE']));

		Lang::load('common.yml', 'common');
		Lang::load('model.yml', 'model');
		Lang::load('page.yml', 'page');
		Lang::load('message.yml', 'message');
	}
}
