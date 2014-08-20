<?php

namespace Base;

class Logger extends \Monolog\Logger
{
	/**
	 * 企業情報を格納するプロパティ
	 * @var \Model_Company
	 */
	private $company;

	/**
	 * クラス初期化時に設定をロード
	 * @return void
	 */
	public static function _init()
	{
		\Config::load('base', true);
	}

	/**
	 * コンストラクタ
	 * 
	 * 設定と与えられた企業モデルに応じて保存先のファイル名を編集する
	 * 企業ごとにログファイルを分けたいので、企業モデルのインスタンスを受け取る
	 * また、識別子を渡すことができるので、機能毎にログをフィルタしたいときなどはそちらを使用すること
	 * 
	 * ## 実行コード(サンプル)
	 * ```php
	 * <?php
	 * $company       = Model_Company::find(1);
	 * $log_hogehoge  = new Base\Logger($company, 'hogehoge'); // 第一引数に企業、第二引数に識別子を渡せる
	 * $log           = new Base\Logger($company);             // 省略すると識別子はなしになる
	 * 
	 * $log_hogehoge->info('xxxxx');
	 * $log_hogehoge->debug('hogehoge');
	 * $log->notice('xaissia');
	 * $log_hogehoge->warn('XXXXXXXXIIIIIIII');
	 * ```
	 * 
	 * ## 上記コードで生成される結果のログファイル（例）
	 * ```
	 * [2014-08-19 15:58:35] hogehoge.INFO: xxxxx [] []
	 * [2014-08-19 15:59:23] hogehoge.DEBUG: hogehoge [] []
	 * [2014-08-19 16:01:56] .NOTICE: xaissia [] []
	 * [2014-08-19 16:02:26] hogehoge.WARNING: XXXXXXXXIIIIIIII [] []
	 * ```
	 * 
	 * @param Model_Company $company    企業モデル
	 * @param string        $identifier ログに付与する識別子、省略すると空文字列
	 */
	public function __construct(\Model_Company $company, $identifier = '')
	{
		parent::__construct($identifier);
		$this->company = $company;

		$log_path  = APPPATH . \Config::get('base.log.base_path') . DS . $company->id . DS;
		$log_file  = date(\Config::get('base.log.file_name')) . '.log';
		$log_level = \Config::get('base.log.level');

		if(!file_exists($log_path)) {
			mkdir($log_path, 0666, true);	// 企業ディレクトリが無ければ作成
		}

		$handler   = new \Monolog\Handler\StreamHandler($log_path.$log_file, $log_level);
		$this->pushHandler($handler);
	}
}
