<?php
/**
 * @author Shingo Inoue<inoue.shingo@hamee.co.jp>
 */

namespace Base;

/**
 * コントローラの基底クラス。
 * 全画面で共通処理として必要な言語ファイルのロード、表示言語のセットを行う。
 * 
 * NOTE: Controllerとコアと同名のクラスにするとこちらが先にロードされてしまうのでControllerという命名を避けている
 */
abstract class Controller_Base extends \Controller_Template
{
	protected $company = null;
	protected $user = null;

	/**
	 * アクセスされたユーザの言語ロケールを取得する
	 * 
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
		return \Str::sub(trim($locales[0]), 0, 2);
	}

	/**
	 * 言語の設定、言語ファイルの読み込みを行う
	 * 
	 * ここに書いておくことで継承クラスは言語ファイルのロードを考慮せずコーディングできる
	 * このクラスを継承したクラスで_initを定義したい場合には、"必ず"parent::_init()をコールして下さい
	 * 
	 * @return void
	 */
	public static function _init() {
		// 言語のセット
		$locale = self::getLocale($_SERVER['HTTP_ACCEPT_LANGUAGE']);
		\Config::set('language', $locale);

		// 言語ファイルのロード
		\Lang::load('common.yml', 'common');
		\Lang::load('model.yml', 'model');
		\Lang::load('page.yml', 'page');
		\Lang::load('message.yml', 'message');
	}

	/**
	 * アクションの実行前に企業情報とユーザ情報をセットする
	 * 
	 * ただしログイン済みの場合のみ。未ログインの場合はプロパティはnullのままです。
	 * この仕様を利用してログイン判定をすることができます。
	 * が、Controller_Neapiクラスを継承しているならその処理を記述済みなのであえて書く必要はありません。
	 * 
	 * Fuelのドキュメントにもありますが、互換性を維持するため継承したクラスでbeforeメソッドを使用する場合には必ずparent::before()をコールして下さい。
	 * 
	 * @return void
	 */
	public function before()
	{
		parent::before();

		$key_company = \Config::get('session.keys.ACCOUNT_COMPANY');
		$key_user    = \Config::get('session.keys.ACCOUNT_USER');

		$company_id = \Session::get($key_company);
		$user_id    = \Session::get($key_user);

		// ログインしていなければnull、していればそれぞれのインスタンスをセットする
		if(!is_null($company_id) && !is_null($user_id)) {
			$this->company = \Model_Company::find($company_id);
			$this->user    = \Model_User::find($user_id);
		}
	}
}
