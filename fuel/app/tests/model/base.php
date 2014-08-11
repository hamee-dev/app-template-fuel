<?php

require_once __DIR__.'/../common.php';

// testsテーブルを用いてデータをDBに保存するテストを行うためのモデル
class Model_Test extends Model_Base {
	public $int_column;
	public $varchar_column;
	public $test_column;
	public $bigint_column;
	public $bool_column;

	protected static $ignoreSaveKey = array();

	protected function before_insert() {}
	protected function before_update() {}
	protected function before_save() {}
	protected function before_delete() {}
	protected function after_insert($success) {}
	protected function after_update($success) {}
	protected function after_save($success) {}
	protected function after_delete($success) {}
}

/**
 * Model_Baseのテスト
 */
class Test_Model_Base extends Test_Common
{
	protected $restore_tables = array('tests');

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		// テスト用DBを定義
		\DBUtil::create_table('tests', array(
			'id' 				=> array('constraint' => 11,  'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
			'int_column'		=> array('type' => 'int'),
			'varchar_column'	=> array('type' => 'varchar', 'constraint' => 50),
			'test_column'		=> array('type' => 'text'),
			'bigint_column'		=> array('type' => 'bigint'),
			'bool_column'		=> array('type' => 'bool'),
			'updated_at' 		=> array('type' => 'timestamp', 'default' => \DB::expr('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')),
			'created_at' 		=> array('type' => 'timestamp', 'default' => \DB::expr('CURRENT_TIMESTAMP')),

		), array('id'), true, 'InnoDB');
	}

	// ------------------------------------------------------------
	//  ▼ ユーティリティ ▼
	// ------------------------------------------------------------
	private function getGetTableName($class_name) {
		$ref = new ReflectionClass($class_name);
		$_getTableName = $ref->getMethod('_getTableName');
		$_getTableName->setAccessible(true);

		return $_getTableName;
	}

	private function getHookMock($classname, $hookname) {
		$mock = $this->getMock($classname, array($hookname));
		$mock->expects($this->once())->method($hookname);
		return $mock;
	}

	private function getHookFirstArgument($classname, $hookname, $expected) {
		$mock = $this->getMock($classname, array($hookname));

		$mock->expects($this->once())
			->method($hookname)
			->with($expected);

		return $mock;
	}

	// ------------------------------------------------------------
	//  ▼ テストコード ▼
	// ------------------------------------------------------------

	// __construct()
	function test___construct_第一引数は省略可能() {
		$model = new Model_Test();
		$this->assertInstanceOf('Model_Base', $model);
	}
	function test___construct_第一引数に連想配列を渡すとそのキーと値がプロパティに設定される() {
		$model = new Model_Test(array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
		));

		$this->assertEquals($model->a, 1);
		$this->assertEquals($model->b, 2);
		$this->assertEquals($model->c, 3);
	}

	// forge()
	// FIXME: __constructをモック化してテストする方法が分からない
	// function test_forge_内部で__constructが呼ばれる() {}

	// isNew()
	function test_isNew_まだDBに存在しないデータならtrueを返す() {
		$model = new Model_Test();
		$this->assertTrue($model->isNew());
	}
	function test_isNew_既にDBに存在しているデータならfalseを返す() {
		$model = Model_Test::find(1);
		$this->assertFalse($model->isNew());
	}

	// validate()
	function test_validate_内部でbefore_validateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_validate');
		$mock->validate();
	}
	function test_validate_内部でafter_validateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_validate');
		$mock->validate();
	}

	// create()
	function test_create_内部でbefore_insertフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_insert');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->insert(); } catch(Exception $e) {}
	}
	// FIXME: コールされたか試すにはMockが必要だが、Mockでupdate()しようとするとコケるのでafter処理がされない
	// function test_create_内部でafter_insertフックが呼ばれる() {
	// 	$mock = $this->getHookMock('Model_Test', 'after_insert');

	// 	// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
	// 	try { $mock->insert(); } catch(Exception $e) {}
	// }

	// insert()
	function test_insert_DBに挿入が行える() {
		$rows = Model_Test::count();

		$model = new Model_Test(array(
			'int_column' => 1,
			'varchar_column' => 'hoge',
			'test_column' => 'hogehoge',
			'bigint_column' => 123,
			'bool_column' => false,
		));
		$model->insert();

		$after_insert_rows = Model_Test::count();
		$this->assertEquals($rows + 1, $after_insert_rows);
	}
	function test_insert_戻り値はboolean() {
		$model = Model_Test::find(1);
		$this->assertTrue(is_bool($model->insert()));
	}
	function test_insert_created_atにNOW（）_updated_atにもNOW（）の値が格納されている() {
		$model = new Model_Test(array(
			'int_column' => 1,
			'varchar_column' => 'hoge',
			'test_column' => 'hogehoge',
			'bigint_column' => 123,
			'bool_column' => false,
		));
		$model->insert();

		$now = DB::query('SELECT NOW()')->execute()->as_array();
		$now = $now[0]['NOW()'];

		// NOTE: insertではcreated_at、updated_atが設定されないので再度取得しなおす
		$model = Model_Test::find($model->id);

		$this->assertEquals($model->created_at, $now);
		$this->assertEquals($model->updated_at, $now);
	}
	// FIXME: id重複の例外が起きない
	function test_insert_一度insertしたモデルを再度insertしようとすると例外が発生する() {
		$model = new Model_Test(array(
			'int_column' => 1,
			'varchar_column' => 'hoge',
			'test_column' => 'hogehoge',
			'bigint_column' => 123,
			'bool_column' => false,
		));
		$model->insert();

		$this->setExpectedException('Exception');
		$model->insert();
	}

	// update()
	function test_update_内部でbefore_updateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_update');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->update(); } catch(Exception $e) {}
	}
	function test_update_内部でbefore_saveフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_save');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->update(); } catch(Exception $e) {}
	}
	// FIXME: コールされたか試すにはMockが必要だが、Mockでupdate()しようとするとコケるのでafter処理がされない
	// function test_update_内部でafter_updateフックが呼ばれる() {
	// 	$mock = $this->getHookMock('Model_Test', 'after_update');

	// 	// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
	//	try { $mock->update(); } catch(Exception $e) {}
	// }
	function test_update_DBに存在するモデルをupdateしてもDBの行数は変わらない() {
		$rows = Model_Test::count();

		$model = Model_Test::find(1);
		$model->update();

		$after_update_rows = Model_Test::count();
		$this->assertEquals($rows, $after_update_rows);
	}
	function test_update_変更した値がDBに反映される() {
		$rows = Model_Test::count();

		$model = Model_Test::find(1);
		$model->varchar_column = 'rewrite from test!!';
		$model->update();

		$this->assertEquals($model, Model_Test::find(1));
	}
	function test_update_updated_atは更新され_created_atは更新されない() {
		$models = Model_Test::findAll();
		$model = $models[0];

		$model->varchar_column = 'rewrite from test';
		sleep(2);	// NOTE: 2秒待って確実にタイムスタンプに変化を起こさせる
		$model->update();

		$updated_model = Model_Test::find($model->id);

		$this->assertTrue($model->updated_at !== $updated_model->updated_at);
		$this->assertEquals($model->created_at, $updated_model->created_at);
	}
	function test_update_戻り値はboolean() {
		$model = Model_Test::find(1);
		$this->assertTrue(is_bool($model->update()));

		$model = new Model_Test();
		$this->assertTrue(is_bool($model->update()));
	}

	// save()
	function test_save_内部でbefore_saveフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_save');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->save(); } catch(Exception $e) {}
	}
	// FIXME: コールされたか試すにはMockが必要だが、Mockでupdate()しようとするとコケるのでafter処理がされない
	// function test_save_内部でafter_saveフックが呼ばれる() {
	// 	$mock = $this->getHookMock('Model_Test', 'after_save');

	// 	// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
	// 	try { $mock->save(); } catch(Exception $e) {}
	// }
	function test_save_新規挿入の際にはcreated_atとupdated_atにNOWの値（CURRENT_TIMESTAMP）が格納される() {
		$now = DB::query('SELECT NOW()')->execute()->as_array();
		$now = $now[0]['NOW()'];

		$model = new Model_Test(array(
			'int_column' => 1,
			'varchar_column' => 'hoge',
			'test_column' => 'hogehoge',
			'bigint_column' => 123,
			'bool_column' => false,
		));
		$model->insert();

		$model = Model_Test::find($model->id);	// NOTE: updated_atとcreated_atが更新されないので再取得

		$this->assertEquals($model->created_at, $now);
		$this->assertEquals($model->updated_at, $now);
	}
	function test_save_更新の際にはcreated_atは変化せずupdated_atの値だけ更新される() {
		$models = Model_Test::findAll();
		$model = $models[0];

		$model->varchar_column = 'rewrite from test';
		sleep(2);	// NOTE: 2秒待って確実にタイムスタンプを変化させる
		$model->save();

		$updated_model = Model_Test::find($model->id);

		$this->assertEquals($model->created_at, $updated_model->created_at);
		$this->assertTrue($model->updated_at !== $updated_model->updated_at);
	}

	// delete()
	function test_delete_内部でbefore_deleteフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_delete');
		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->delete(); } catch(Exception $e) {}
	}
	// FIXME: コールされたか試すにはMockが必要だが、Mockでupdate()しようとするとコケるのでafter処理がされない
	// function test_delete_内部でafter_deleteフックが呼ばれる() {
	// 	$mock = $this->getHookMock('Model_Test', 'after_delete');
	// 	// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
	// 	try { $mock->delete(); } catch(Exception $e) {}
	// }
	function test_delete_DBから削除ができる() {
		$rows = Model_Test::count();
		$model = Model_Test::find(1);
		$model->delete();

		$this->assertEquals($rows - 1, Model_Test::count());
	}
	function test_delete_戻り値はboolean() {
		$model = Model_Test::find(1);
		$this->assertTrue(is_bool($model->delete()));

		$model = new Model_Test();
		$this->assertTrue(is_bool($model->delete()));
	}

	// find()
	// FIXME: staticメソッドがコールされたかどうかのチェックの方法が分からない
	// function test_find_after_findフックが呼ばれる() {}
	// function test_find_after_findフックの戻り値がfindの戻り値になる() {}
	function test_find_DBからデータを取得して生成したインスタンスはid_created_at_updated_atを持っている() {
		$model = Model_Test::find(1);

		$this->assertTrue(isset($model->id));
		$this->assertTrue(isset($model->created_at));
		$this->assertTrue(isset($model->updated_at));
	}
	function test_find_存在しないidを与えるとnullが返る() {
		$unknown_id = 100000000;
		$model = Model_Test::find($unknown_id);
		$this->assertEquals($model, null);
	}

	// findBy()
	function test_findBy_一致する条件があればModel_Baseのインスタンスの配列が返る() {
		$result = Model_Test::findBy('varchar_column', 'Hoge');

		$this->assertTrue(is_array($result));

		foreach($result as $instance) {
			$this->assertInstanceOf('Model_Base', $instance);
		}
	}
	function test_findBy_存在しない条件を与えると空配列が返る() {
		$result = Model_Test::findBy('varchar_column', 'Unknown valuevaluevalue');

		$this->assertTrue(is_array($result));
		$this->assertEmpty($result);
	}

	// findLike()
	function test_findLike_部分一致で検索ができる() {
		$target = Model_Test::findLike('varchar_column', 'H');

		$results = array();
		$results[] = Model_Test::findLike('varchar_column', 'o');
		$results[] = Model_Test::findLike('varchar_column', 'g');
		$results[] = Model_Test::findLike('varchar_column', 'e');

		foreach($results as $result) {
			$this->assertEquals($target[0]->toArray(), $result[0]->toArray());
		}

		// カラムに含まれない文字で検索してもマッチしない
		$not_matched = Model_Test::findLike('varchar_column', 'xx');
		$this->assertEmpty($not_matched);
	}

	// count()
	function test_count_引数を省略するとそのテーブル全件のデータ件数が取得できる() {
		$rows = Model_Test::count();

		$all_row_count = Model_Test::count();
		$this->assertEquals($rows, $all_row_count);
	}
	function test_count_存在しない条件を与えると戻り値はゼロ() {
		$not_exist_condition = array(array('id', '=', 10000));
		// NOTE: false=重複行も１行ずつカウントする
		$matched_row_count = Model_Test::count('id', false, $not_exist_condition);

		$this->assertEquals(0, $matched_row_count);
	}

	// transactionDo()
	// FIXME: staticメソッドのコール確認の方法がわからない
	// function test_transactionDo_トランザクションが実行されている() {}
	// function test_transactionDo_コールバックで例外が起きずに終了するとコミットされる() {}
	// function test_transactionDo_コールバック内で例外を投げるとロールバックされる() {}

	// _getTableName()
	// NOTE: クラス名->テーブル名の変換ルールについてはこちらを参照
	// http://ne0.next-engine.org:10080/ld3sl/issues/6297#クラス名とテーブル名の命名規約
	function test__getTableName_クラス名からModel_を取り除き小文字かつ複数形にした文字列が返却される() {
		$table_name = $this->getGetTableName('Model_Test')->invokeArgs(null, array());
		$this->assertEquals('tests', $table_name);
	}

	// toArray()
	function test_toArray_戻り値は連想配列() {
		$model = new Model_Test();
		$this->assertTrue(is_array($model->toArray()));
	}
	function test_toArray_ignoreされたプロパティは返却されない() {
		$model = new Model_Test();
		$result = $model->toArray();
		$this->assertFalse(isset($result['hoge']));
	}
	function test_toArray_定義されていないプロパティは返却されない() {
		$model = new Model_Test(array(
			'unknown' => 1,
			'not_exist' => 1
		));

		$result = $model->toArray();
		$this->assertFalse(isset($result['unknown']));
		$this->assertFalse(isset($result['not_exist']));
	}
	// NOTE: toArrayは_getOriginalPropertysのpublicエイリアス
	// function test__getOriginalPropertys_() {}
}
