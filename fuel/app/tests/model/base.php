<?php

require_once __DIR__.'/../usedb.php';

// testsテーブルを用いてデータをDBに保存するテストを行うためのモデル
class Model_Test extends Model_Base {
	public $hoge = 1;
	protected static $ignoreSaveKey = array('hoge');

	public $content = null;
}

/**
 * Model_Baseのテスト
 */
class Test_Model_Base extends Usedb
{
	/**
	 * ユーティリティ
	 */
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

	/**
	 * テストコード
	 */
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

	// FIXME: __constructをモック化してテストする方法が分からない
	// function test_forge_内部で__constructが呼ばれる() {}

	function test_isNew_まだDBに存在しないデータならtrueを返す() {
		$model = new Model_Test();
		$this->assertTrue($model->isNew());
	}
	function test_isNew_既にDBに存在しているデータならfalseを返す() {
		$model = Model_Test::find(1);
		$this->assertFalse($model->isNew());
	}

	function test_validate_内部でbefore_validateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_validate');
		$mock->validate();
	}
	function test_validate_内部でafter_validateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_validate');
		$mock->validate();
	}

	function test_create_内部でbefore_insertフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_insert');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->insert(); } catch(Exception $e) {}
	}
	function test_create_内部でafter_insertフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_insert');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->insert(); } catch(Exception $e) {}
	}
	function test_create_DBに挿入が行える() {
		$rows = $this->getConnection()->getRowCount('tests');

		$model = new Model_Test(array(
			'content' => 'hogehogehoge'
		));
		$model->insert();

		$after_insert_rows = $this->getConnection()->getRowCount('tests');
		$this->assertEquals($rows + 1, $after_insert_rows);
	}
	function test_create_戻り値はboolean() {
		$model = Model_Test::find(1);
		$this->assertTrue(is_bool($model->insert()));

		$model = new Model_Test();
		$this->assertTrue(is_bool($model->insert()));
	}

	function test_update_内部でbefore_updateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_update');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->update(); } catch(Exception $e) {}
	}
	function test_update_内部でafter_updateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_update');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->update(); } catch(Exception $e) {}
	}
	function test_update_DBの行数は変わらない() {
		$rows = $this->getConnection()->getRowCount('tests');

		$model = Model_Test::find(1);
		$model->update();

		$after_update_rows = $this->getConnection()->getRowCount('tests');
		$this->assertEquals($rows, $after_update_rows);
	}
	function test_update_変更した値がDBに反映される() {
		$rows = $this->getConnection()->getRowCount('tests');
		$model = Model_Test::find(1);

		$model->content = 'rewrite from test!!';

		$model->update();
		$this->assertEquals($model, Model_Test::find(1));
	}
	function test_update_戻り値はboolean() {
		$model = Model_Test::find(1);
		$this->assertTrue(is_bool($model->update()));

		$model = new Model_Test();
		$this->assertTrue(is_bool($model->update()));
	}

	function test_save_内部でbefore_saveフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_save');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->save(); } catch(Exception $e) {}
	}
	function test_save_内部でafter_saveフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_save');

		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->save(); } catch(Exception $e) {}
	}

	function test_delete_内部でbefore_deleteフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_delete');
		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->delete(); } catch(Exception $e) {}
	}
	function test_delete_内部でafter_deleteフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_delete');
		// NOTE: モックでインスタンスを生成するとクラス名が変わりテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->delete(); } catch(Exception $e) {}
	}
	function test_delete_DBから削除ができる() {
		$rows = $this->getConnection()->getRowCount('tests');
		$model = Model_Test::find(1);
		$model->delete();

		$this->assertEquals($rows - 1, $this->getConnection()->getRowCount('tests'));
	}
	function test_delete_戻り値はboolean() {
		$model = Model_Test::find(1);
		$this->assertTrue(is_bool($model->delete()));

		$model = new Model_Test();
		$this->assertTrue(is_bool($model->delete()));
	}

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

	function test_findBy_一致する条件があればModel_Baseのインスタンスの配列が返る() {
		$result = Model_Test::findBy('content', 'Hoge');

		$this->assertTrue(is_array($result));

		foreach($result as $instance) {
			$this->assertInstanceOf('Model_Base', $instance);
		}
	}
	function test_findBy_存在しない条件を与えると空配列が返る() {
		$result = Model_Test::findBy('content', 'Unknown valuevaluevalue');

		$this->assertTrue(is_array($result));
		$this->assertEmpty($result);
	}

	function test_findLike_部分一致で検索ができる() {
		$target = Model_Test::findLike('content', 'H');

		$results = array();
		$results[] = Model_Test::findLike('content', 'o');
		$results[] = Model_Test::findLike('content', 'g');
		$results[] = Model_Test::findLike('content', 'e');

		foreach($results as $result) {
			$this->assertEquals($target[0]->toArray(), $result[0]->toArray());
		}

		// カラムに含まれない文字で検索してもマッチしない
		$not_matched = Model_Test::findLike('content', 'xx');
		$this->assertEmpty($not_matched);
	}

	function test_count_引数を省略するとそのテーブル全件のデータ件数が取得できる() {
		$rows = $this->getConnection()->getRowCount('tests');

		$all_row_count = Model_Test::count();
		$this->assertEquals($rows, $all_row_count);
	}
	function test_count_存在しない条件を与えると戻り値はゼロ() {
		$not_exist_condition = array(array('id', '=', 10000));
		// NOTE: false=重複行も１行ずつカウントする
		$matched_row_count = Model_Test::count('id', false, $not_exist_condition);

		$this->assertEquals(0, $matched_row_count);
	}

	// FIXME: staticメソッドのコール確認の方法がわからない
	// function test_transactionDo_トランザクションが実行されている() {}
	// function test_transactionDo_コールバックで例外が起きずに終了するとコミットされる() {}
	// function test_transactionDo_コールバック内で例外を投げるとロールバックされる() {}

	// NOTE: クラス名->テーブル名の変換ルールについてはこちらを参照
	// http://ne0.next-engine.org:10080/ld3sl/issues/6297#クラス名とテーブル名の命名規約
	function test__getTableName_クラス名からModel_を取り除き、小文字かつ複数形にした文字列が返却される() {
		$table_name = $this->getGetTableName('Model_Test')->invokeArgs(null, array());
		$this->assertEquals('tests', $table_name);
	}

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
