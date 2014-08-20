<?php

require_once __DIR__.'/../common.php';

/**
 * Controller_Baseのテスト
 */
class Test_Controller_Base extends Test_Common
{
	// 言語の設定をリセット
	public function setUp() {
		parent::setUp();

		// CLIだとundefined indexと言われるので明示的にnullを設定
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = null;

		Config::set('language', null);
	}

	private function getGetLocale() {
		return parent::getMethod('Controller_Base', 'getLocale');
	}
	public function test_getLocale_カンマ区切りも文字列が与えられても先頭の２文字だけ見る() {
		$expected = 'ja';
		$getLocale = $this->getGetLocale();

		$lang = $getLocale->invokeArgs(null, array('ja,en,kr,in'));
		$this->assertEquals($expected, $lang);
	}
	public function test_getLocale_先頭に空白文字が入っていたら無視し先頭の２文字を返す() {
		$expected = 'ja';
		$getLocale = $this->getGetLocale();

		$lang = $getLocale->invokeArgs(null, array("  \t\nja"));
		$this->assertEquals($expected, $lang);
	}

	// FIXME: テストディレクトリ内だけで言語ファイルを完結させる方法が分からない
	public function test_setLanguage_言語を設定しない場合は日本語が選択される() {}
	public function test_setLanguage_jaを指定すると日本語が選択される() {}
	public function test_setLanguage_enを指定すると英語が選択される() {}

	public function test_init_を呼ぶと言語ファイルcommon_ymlの内容が使用できる() {
		$this->assertTrue(!is_null(Lang::get('common')));
	}
	public function test_init_を呼ぶと言語ファイルmodel_ymlの内容が使用できる() {
		$this->assertTrue(!is_null(Lang::get('model')));
	}
	public function test_init_を呼ぶと言語ファイルpage_ymlの内容が使用できる() {
		$this->assertTrue(!is_null(Lang::get('page')));
	}
	public function test_init_を呼ぶと言語ファイルmessage_ymlの内容が使用できる() {
		$this->assertTrue(!is_null(Lang::get('message')));
	}
}
