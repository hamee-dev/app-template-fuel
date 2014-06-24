<?php

// testsテーブルを用いてデータをDBに保存するテストを行うためのモデル
class Model_Test extends Model_Base {
	public $hoge = 1;
	protected static $ignore_save_key = array('hoge');

	public $content = null;
}

/**
 * Model_Baseのテスト
 */
class Test_Model_Base extends PHPUnit_Extensions_Database_TestCase
{
	static private $pdo = null;
	private $conn = null;

    /**
     * フィクスチャの定義
     */
	final public function getConnection() {
		// FIXME: configから設定を読み込む
		$dsn = 'mysql:host=192.168.56.110;port=3306;dbname=ne_base';
		$username = 'root';
		$password = 'hamee831';

		if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO($dsn, $username, $password);
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, 'ne_base');
        }
        return $this->conn;
    }

	public function getDataSet() {
		return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(__DIR__."/seed.yml");
    }

    /**
     * ユーティリティ
     */
	private function getHookMock($classname, $hookname) {
		$mock = $this->getMock($classname, array($hookname));
		$mock->expects($this->once())->method($hookname);
		return $mock;
	}

	private function getHookFirstArgument($classname, $hookname, $expected) {
		$mock = $this->getMock($classname, array($hookname));
		$mock->expects($this->once())->method($hookname)->with($expected);
		return $mock;
	}

    /**
     * テストコード
     */
	function test___construct_第一引数は省略可能() {
		$model = new Model_Base();
		$this->assertInstanceOf('Model_Base', $model);
	}
	function test___construct_第一引数に連想配列を渡すとそのキーと値がプロパティに設定される() {
		$model = new Model_Base(array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
		));

		$this->assertEquals($model->a, 1);
		$this->assertEquals($model->b, 2);
		$this->assertEquals($model->c, 3);
	}

	// function test_forge_内部で__constructが呼ばれる() {}

	function test_isNew_newでインスタンス作成したらtrue() {
		$model = new Model_Base();
		$this->assertTrue($model->isNew());
	}
	// function test_isNew_DBから取得したデータでインスタンス作成したらfalse() {}

	function test_validate_before_validateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_validate');
		$mock->validate();
	}
	function test_validate_after_validateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_validate');
		$mock->validate();
	}

	function test_create_before_insertフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_insert');
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->insert(); } catch(Exception $e) {}
	}
	function test_create_after_insertフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_insert');
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->insert(); } catch(Exception $e) {}
	}
	function test_create_DBに挿入が行える() {
		$rows = $this->getConnection()->getRowCount('tests');
		$model = new Model_Test(array(
			'content' => 'hogehogehoge'
		));
		$model->insert();

		$this->assertEquals($rows + 1, $this->getConnection()->getRowCount('tests'));
	}
	function test_create_戻り値はboolean() {
		$model = Model_Test::find(1);
		$this->assertTrue(is_bool($model->insert()));

		$model = new Model_Test();
		$this->assertTrue(is_bool($model->insert()));
	}

	function test_update_before_updateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_update');
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->update(); } catch(Exception $e) {}
	}
	function test_update_after_updateフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_update');
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->update(); } catch(Exception $e) {}
	}
	function test_update_updateしてもDBの行数は増えない() {
		$rows = $this->getConnection()->getRowCount('tests');
		$model = Model_Test::find(1);

		$model->update();
		$this->assertEquals($rows, $this->getConnection()->getRowCount('tests'));
	}
	function test_update_プロパティを更新してupdateするとそのカラムの値が変化している() {
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

	function test_save_before_saveフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'before_save');
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->save(); } catch(Exception $e) {}
	}
	function test_save_after_saveフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_save');
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->save(); } catch(Exception $e) {}
	}
	// function test_save_newしたインスタンスならinsertが呼ばれる() {}
	// function test_save_DBから取得したインスタンスならupdateが呼ばれる() {}

	function test_delete_before_deleteフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_delete');
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->delete(); } catch(Exception $e) {}
	}
	function test_delete_after_deleteフックが呼ばれる() {
		$mock = $this->getHookMock('Model_Test', 'after_delete');
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
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

	// function test_find_after_findフックが呼ばれる() {}
	// function test_find_after_findフックの戻り値がfindの戻り値になる() {}
	function test_find_取得したインスタンスはid_created_at_updated_atを持っている() {
		$model = Model_Test::find(1);

		$this->assertTrue(isset($model->id));
		$this->assertTrue(isset($model->created_at));
		$this->assertTrue(isset($model->updated_at));
	}
	function test_find_存在しないidを与えるとnullが返る() {
		$model = Model_Test::find(100000000);
		$this->assertEquals($model, null);
	}

	function test_findBy_Model_Baseのインスタンスの配列が返る() {
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
		$this->assertEquals($rows, Model_Test::count());
	}
	function test_count_存在しない条件を与えると戻り値はゼロ() {
		$this->assertEquals(0, Model_Test::count('id', true, array(array('id', '=', 10000))));
	}

	function test_before_save_第一引数はクエリビルダー() {
		$mock = $this->getHookFirstArgument('Model_Test', 'before_save', $this->isInstanceOf('Database_Query_Builder'));
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->save(); } catch(Exception $e) {}
	}
	function test_before_insert_第一引数はクエリビルダー() {
		$mock = $this->getHookFirstArgument('Model_Test', 'before_insert', $this->isInstanceOf('Database_Query_Builder'));
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->insert(); } catch(Exception $e) {}
	}
	function test_before_update_第一引数はクエリビルダー() {
		$mock = $this->getHookFirstArgument('Model_Test', 'before_update', $this->isInstanceOf('Database_Query_Builder'));
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->update(); } catch(Exception $e) {}
	}
	function test_before_delete_第一引数はクエリビルダー() {
		$mock = $this->getHookFirstArgument('Model_Test', 'before_delete', $this->isInstanceOf('Database_Query_Builder'));
		// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
		try { $mock->delete(); } catch(Exception $e) {}
	}

	// function test_after_find_戻り値はDBから取得したデータの連想配列() {}

	// function test_after_save_第一引数はboolean() {
	// 	$mock = $this->getHookFirstArgument('Model_Test', 'after_save', $this->isType('boolean'));
	// 	// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
	// 	try { $mock->save(); } catch(Exception $e) {}
	// }
	// function test_after_insert_第一引数はboolean() {
	// 	$mock = $this->getHookFirstArgument('Model_Test', 'after_insert', $this->isType('boolean'));
	// 	// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
	// 	try { $mock->insert(); } catch(Exception $e) {}
	// }
	// function test_after_update_第一引数はboolean() {
	// 	$mock = $this->getHookFirstArgument('Model_Test', 'after_update', $this->isType('boolean'));
	// 	// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
	// 	try { $mock->update(); } catch(Exception $e) {}
	// }
	// function test_after_delete_第一引数はboolean() {
	// 	$mock = $this->getHookFirstArgument('Model_Test', 'after_delete', $this->isType('boolean'));
	// 	// NOTE: モックで生成するとクラス名が変わるのでテーブルがないと例外を投げられる。ので握りつぶし
	// 	try { $mock->delete(); } catch(Exception $e) {}
	// }
	// function test_after_validate_第一引数はboolean() {
	// 	$mock = $this->getHookFirstArgument('Model_Test', 'after_validate', $this->isType('boolean'));
	// 	$mock->validate();
	// }

	// function test_transactionDo_トランザクションが実行されている() {}
	// function test_transactionDo_コールバックで例外が起きずに終了するとコミットされる() {}
	// function test_transactionDo_コールバック内で例外を投げるとロールバックされる() {}

	private function getGetTableName($class_name) {
		$ref = new ReflectionClass($class_name);
		$_getTableName = $ref->getMethod('_getTableName');
		$_getTableName->setAccessible(true);

		return $_getTableName;
	}
	function test__getTableName_Model_を取り除き、クラス名を小文字かつ複数形にした文字列が返却される() {
		$this->assertEquals('tests', $this->getGetTableName('Model_Test')->invokeArgs(null, array()));
	}

	function test_toArray_戻り値は連想配列() {
		$model = new Model_Test();
		$this->assertTrue(is_array($model->toArray()));
	}
	function test_toArray_ignoreされたプロパティは含まれない() {
		$model = new Model_Test();
		$result = $model->toArray();
		$this->assertFalse(isset($result['hoge']));
	}
	function test_toArray_コンストラクタで渡されてもプロパティ定義されていないプロパティが含まれない() {
		$model = new Model_Test(array('unknown' => 1, 'not_exist' => 1));
		$result = $model->toArray();
		$this->assertFalse(isset($result['unknown']));
		$this->assertFalse(isset($result['not_exist']));
	}
	// NOTE: toArrayは_getOriginalPropertysのpublicエイリアス
	// function test__getOriginalPropertys_() {}
}
