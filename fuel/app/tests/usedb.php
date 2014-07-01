<?php

class Usedb extends PHPUnit_Extensions_Database_TestCase
{
	private static $pdo;
	private static $conn = null;
	private static $DB_NAME = null;

	/**
	 * フィクスチャの定義
	 */
	// テスト実行前に一度だけ設定を読み込みPDOのインスタンスを作成する
	public static function setUpBeforeClass() {
		// loadをしないとDBクラスが使用されるまで設定が読み込まれないので明示的に読み込み
		Config::load('db', true);
		$config = Config::get('db.default.connection');

		// DSNからデータベース名を取得
		preg_match('/dbname=(\w+)$/', $config['dsn'], $matched);
		self::$DB_NAME = $matched[1];

		self::$pdo = new PDO($config['dsn'], $config['username'], $config['password']);
	}
	public static function tearDownAfterClass() {
		self::$pdo = null;
		self::$conn = null;
	}

	// テストケースごとのフィクスチャのリセット(PHPUnitが勝手に使う)や、
	// テストケース内で行数の取得など、DBクラスを用いずDBに接続したい場合に使用する
	public function getConnection() {
		// 接続を使いまわす
		if(is_null(self::$conn)) {
			self::$conn = $this->createDefaultDBConnection(self::$pdo, self::$DB_NAME);
		}
		return self::$conn;
	}

	// DBの構造をseed.ymlの内容でリセットする
	public function getDataSet() {
		return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(__DIR__."/seed.yml");
	}
}
