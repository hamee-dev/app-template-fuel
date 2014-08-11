<?php

use Fuel\Core\TestCase;

/**
 * テストクラスの基底クラス
 * データベースのユーティリティ、リフレクションの汎用処理、アサーションのユーティリティなどを定義する
 */
abstract class Test_Common extends TestCase
{
	/**
	 * fuel/app/configディレクトリから、シードデータまでの相対パス
	 * @var string
	 */
	const SEED_PATH = '../tests/seed.yml';

	/**
	 * シードデータを格納する
	 * @var array
	 */
	protected static $seeds = array();

	/**
	 * setUp時にシードデータでリフレッシュするテーブルを配列で指定
	 * @var array
	 */
	protected $restore_tables = array();

	/**
	 * ## テスト開始時の初期化処理
	 * テスト開始時にシードデータを読み込み、その結果をプロパティにキャッシュしておく
	 * NOTE: PHPUnitの都合でこのクラスが継承されるたびに1回の初期化が起こるため、既に設定をロード済みならロードしないようにしている。
	 */
	public static function setUpBeforeClass()
	{
		if(count(static::$seeds) === 0) {
			static::$seeds = Config::load(self::SEED_PATH, true);
		}
	}

	/**
	 * ## テストケース事の初期化処理
	 * $restore_tablesプロパティに値が指定されていたら、そのテーブルを空にして、シードデータを投入する
	 * デフォルトは空配列が指定されているので、何も指定がされなければ何も起きない。
	 * 
	 * ### sample
	 * ```php
	 * class Test_Task_Item extends Test_Common {
	 *     protected $restore_tables = array('items', 'companies');
	 * }
	 * ```
	 */
	public function setUp()
	{
		// 指定されたシードデータを入れなおす
		foreach($this->restore_tables as $table) {
			DBUtil::truncate_table($table);

			// バルクインサート
			if(count(static::$seeds[$table]) > 0) {
				$this->bulkInsert($table, static::$seeds[$table]);
			}
		}
	}

	// ------------------------------------
	// DB Utilities
	// ------------------------------------
	/**
	 * $stepのデフォルトが50件なのは下記の記事に基づく情報。
	 * PHP/MySQL でレコードを N 件ずつバルクインサート http://blog.yuyat.jp/archives/2018
	 * @param string  $table 挿入を行うテーブル名
	 * @param array[] $rows  挿入を行うデータの連想配列（シードから読み取った値を想定）
	 * @param int     $step  一度に挿入する件数。デフォルトでは50件ずつ
	 */
	private function bulkInsert($table, $rows, $step = 50)
	{
		$columns  = array_keys($rows[0]);

		$count    = count($rows);
		$inserted = 0;
		while($inserted < $count) {
			$insert_rows = array_slice($rows, $inserted, $step);

			$q = DB::insert($table);
			$q->columns($columns);

			foreach($insert_rows as $row) {
				$values = array_values($row);
				$q->values($values);
			}
			$q->execute();

			$inserted += $count;
		}
	}

	// ------------------------------------
	// Reflection Utilities
	// ------------------------------------
	protected function getProperty($class, $property)
	{
		$class = new ReflectionClass($class);

		$property = $class->getProperty($property);
		$property->setAccessible(true);

		return $property;
	}

	protected function getMethod($class, $method)
	{
		$class = new ReflectionClass($class);

		$method = $class->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}

	// ------------------------------------
	// Assertion Utilities
	// ------------------------------------
	protected function assertCalledOnce($mock, $method)
	{
		$mock->expects($this->once())->method($method);
		return $mock;
	}
}
