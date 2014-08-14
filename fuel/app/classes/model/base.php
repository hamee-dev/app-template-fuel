<?php

/**
 * アプリのベースとなるモデルクラス。全てのモデルクラスの基点はこのクラスとなる。
 * こいつ自身をインスタンス化する意味が無いのでabstractにしています。
 * 
 * ### クラス名とテーブル名の命名規約
 * クラス名とテーブル名を縛ることで、命名規約に則りさえすれば、楽にモデルのコードを書くことが出来る。
 * 
 * FuelPHPのモデルクラスは'Model_**'となるので、model_を取り払った**を複数形にしたものがテーブル名となる。  
 * ex) Model_Base => 'bases', Model_Test => 'tests', Model_HogeHoge => 'hogehoges'
 * 
 * 
 * ### プロパティ名とカラム名の命名規約
 * Model_Baseは、ignoreSaveKeyプロパティに指定されていないプロパティ名は、自動的に保存しようとします。
 * なので、DBのカラム名とモデルのプロパティは必ず一致させてください。
 * また、カラムなのかモデルのプロパティなのか、明確に分けるための命名規約が以下のとおりです。  
 * 
 * |種類           |命名規則|
 * |DBのカラム      |スネークケース|
 * |モデルのプロパティ|キャメルケース|
 * 
 * と命名規則を分けてください。
 * Model_Baseで言えば、id, created_at, updated_atがカラム名（スネークケース）  
 * validationErrors, ignoreSaveKeyがモデルのプロパティ（キャメルケース）です。
 * 
 * @author Shingo Inoue <inoue.shingo@hamee.co.jp>
 */
abstract class Model_Base
{
	/**
	 * カラムのID
	 * @var string
	 */
	public $id = null;

	/**
	 * DBへの挿入日時
	 * @var string
	 */
	public $created_at = null;

	/**
	 * データの更新日時
	 * @var string
	 */
	public $updated_at = null;

	/**
	 * findで比較に用いる主キー
	 */
	protected static $primaryKey = 'id';

	/**
	 * バリデーション時のエラー情報を格納
	 * @var array
	 */
	protected $validationErrors = array();

	/**
	 * 保存時に除外するプロパティ
	 * ここに指定していないプロパティは、全て保存処理がされてしまうので注意。
	 * NOTE: updated_atは自動更新するので触らない(on UPDATE on update CURRENT_TIMESTAMPが設定されている)
	 * @var string[]
	 */
	protected static $ignoreSaveKey = array(
		'ignoreSaveKey',
		'id',
		'created_at',
		'primaryKey',
		'validationErrors',
	);


	// =======================================
	// 公開メソッド
	// =======================================

	/**
	 * key-valueの連想配列が与えられていたら、そのプロパティに値を代入する。
	 *
	 * @param  array $data データの配列（省略可）
	 * @return void
	 */
	public function __construct(array $data = array()) {
		foreach($data as $property => $value) {
			$this->{$property} = $value;
		}
	}

	/**
	 * データの妥当性をチェックする。
	 * @return boolean バリデーションに成功したらtrue。
	 *                 エラーがあればfalseを返し、validationErrorsにエラーメッセージを格納する
	 */
	public function validate() {
		$this->hook('before_validate');
		$ret = $this->runValidate();
		$this->hook('after_validate', $ret);

		return $ret;
	}

	/**
	 * DBに未挿入のデータか否かをチェックする。
	 * @return bool DBに未挿入のデータならtrue、DBに挿入済みのデータならfalse
	 */
	public function isNew() {
		return is_null($this->id);
	}

	/**
	 * データをDBに挿入する。
	 * 
	 * ※データの検証は行わないので、事前にvalidateで入力チェックをしておくこと。
	 * @param boolean $insert_ignore INSERT IGNOREを使用するか否か(trueなら使用、falseは不使用)
	 * @return boolean
	 */
	public function insert($insert_ignore = false) {
		$table_name = $this->_getTableName();

		$this->hook('before_save');
		$this->hook('before_insert');

		$this->updated_at = \DB::expr('NOW()');
		$query = DB::insert($table_name)->set($this->toArray());

		if($insert_ignore) {
			// NOTE: FuelPHPにINSERT IGNOREをサポートする機能が存在しないため、SQLを置き換える方法で実装。
			$current_sql = $query->compile();

			// SQLが'INSERT'もしくは'insert'で始まっていたら、'INSERT IGNORE'に置き換える。
			if(Str::starts_with($current_sql, 'INSERT', true)) {
				$query = DB::query('INSERT IGNORE'.Str::sub($current_sql, 6), DB::INSERT);
			}
		}

		$ret = $query->execute();

		$result = false;
		// NOTE: 挿入に失敗していたら、idが無いので設定のしようがない
		// NOTE: INSERT IGNOREを使用すると"変化行数がないけど成功"が起こりうるので作用した行数もチェック
		if($ret[1] > 0) {
			$this->id = $ret[0];
			$result = true;
		}

		$this->hook('after_insert', $result);
		$this->hook('after_save', $result);

		return $result;
	}

	/**
	 * データのDB上の更新を行う。
	 * 
	 * ※データの検証は行わないので、事前にvalidateで入力チェックをしておくこと。
	 * @return boolean
	 */
	public function update() {
		$this->hook('before_save');
		$this->hook('before_update');

		$this->updated_at = \DB::expr('NOW()');

		$table_name = $this->_getTableName();
		$query = DB::update($table_name)
					->set($this->toArray())
					->where(self::$primaryKey, $this->{self::$primaryKey});

		// NOTE: $retがintなら更新された行数（成功）、NULLなら失敗
		$ret = $query->execute();
		$result = is_int($ret);

		$this->hook('after_update', $result);
		$this->hook('after_save', $result);

		return $result;
	}

	/**
	 * 物理削除(行をDB上から消す)を行う
	 * @return boolean
	 */
	public function delete() {
		$this->hook('before_delete');

		$table_name = $this->_getTableName();
		$query = DB::delete($table_name)->where(static::$primaryKey, $this->{static::$primaryKey});

		// NOTE: $retがintなら削除された行数（成功）、NULLなら失敗
		$ret = $query->execute();
		$result = is_int($ret);

		$this->hook('after_delete', $result);

		return $result;
	}

	/**
	 * インスタンスが既にDBに挿入されているか否かを考慮せずに呼び出すインタフェースを提供する。
	 * INSERT ON DEPLICATE KEY UPDATE文を実行する。
	 * 公式ドキュメント：http://dev.mysql.com/doc/refman/5.1/ja/insert-on-duplicate.html
	 * 
	 * NOTE: MySQL構文のため、MySQL以外のDBでsaveメソッドを使用することは出来ません。
	 * 
	 * @return bool 挿入or削除に成功したら`true`, 失敗したら`false`
	 */
	public function save() {
		$this->hook('before_save');

		$this->updated_at = \DB::expr('NOW()');

		// FuelPHPにON DUPLICATE KEY UPDATEの機能がサポートされていないので、
		// ON DUPLICATE KEY UPDATEを追記したクエリビルダオブジェクトを生成する
		$query = DB::insert($this->_getTableName())->set($this->toArray());

		// ON DUPLICATE KEY UPDATE以降で更新する値を、自前でなくクエリビルダを利用して生成する
		// FIXME: かなりゴリ押し。テーブル名が大文字でSETとかだった場合にバグります。
		$update_sql    = DB::update($this->_getTableName())->set($this->toArray())->compile();
		$set_pos       = mb_strpos($update_sql, ' SET ');
		$after_set_sql = mb_substr($update_sql, $set_pos + 5);	// ' SET 'の3文字を読み飛ばす

		$insert_or_update_query = DB::query($query->compile().' ON DUPLICATE KEY UPDATE '.$after_set_sql);

		$ret = $insert_or_update_query->execute();

		$result = false;
		if($ret[1] >= 0) {	// NOTE: 0以上にしてデグらない？
			$this->id = $ret[0];
			$result = true;
		}

		$this->hook('after_save', $result);
		return $result;
	}

	/**
	 * インスタンスを連想配列に変換する
	 * @return array
	 */
	public function toArray() {
		$ignores = array_merge(self::$ignoreSaveKey, static::$ignoreSaveKey);

		$ret = array();
		$props = get_class_vars(get_called_class());

		foreach($props as $prop => $value) {
			if(in_array($prop, $ignores)) continue;

			$ret[$prop] = $this->{$prop};
		}
		return $ret;
	}

	/**
	 * Forges new Model_Crud objects.
	 *
	 * @param   array  $data  Model data
	 * @return  Model_Crud
	 */
	public static function forge(array $data = array())
	{
		return new static($data);
	}

	/**
	 * NOTE: 仕様変更される恐れがあります。
	 * 複数の値を与えて、DBにinsertを行う。
	 * 
	 * ### sample
	 * ```php
	 * Model_Base::create(array(
	 * 	'username',
	 * 	'password',
	 * ),
	 * array(
	 * 	array('hogehoge', 'hugahuga', 'foooooooooo'),
	 * 	array('foobar',   'hige',     'fizzbuzz'),
	 * ));
	 * ```
	 * 
	 * ### なぜkey-valueの連想配列を与えないのか
	 * PHPの連想配列はメモリをとても食うため、配列のみで済ましたい。よって第１,第２引数がややこしくなっています。ご了承ください
	 * 
	 * @param  array   $columns       カラム名を列挙した配列。ここで指定したカラム順にvaluesを設定する。
	 * @param  array   $values        値の列挙。１つ１つのカラムに挿入する値を配列で指定する。
	 * @return array   挿入されたインスタンスの配列
	 */
	public static function create(array $columns, array $values) {
		$ret = array();
		// NOTE: デバッグの際に検索しやすいよう、$iではなく重複しにくい$iiを使用している。
		for($ii = 0, $length = count($values[0]); $ii < $length; $ii++) {
			$model = new static();

			// 値をセット
			foreach($columns as $j => $column) {
				$model->{$column} = $values[$j][$ii];
			}

			$model->save();
			$ret[] = $model;
		}

		return $ret;
	}

	/**
	 * 指定されたIDのデータのみ取得する
	 * @param	int	$id 取得したいデータのID
	 * @return mixed 指定されたIDが見つかればモデルのインスタンス、無ければnull
	 */
	public static function find($id) {
		$table_name = (new static())->_getTableName();
		$query = DB::select()
					->from($table_name)
					->where(static::$primaryKey, $id)
					->limit(1)
					->as_object(get_called_class());

		$result = $query->execute()->as_array();
		if(count($result) > 0) {
			$models = static::after_find($result);
			return $models[0];
		} else {
			return null;
		}
	}

	/**
	 * テーブル内のデータを全件取得する
	 * @return array テーブル全行のデータをインスタンス化した要素の配列、１件もデータがない場合は空配列
	 */
	public static function findAll() {
		$table_name = (new static())->_getTableName();
		$query = DB::select()
					->from($table_name)
					->as_object(get_called_class());

		$ret = $query->execute();
		return static::after_find($ret->as_array());
	}

	/**
	 * 条件を１つ指定しデータを取得する
	 * @param  string $column	検索に使用するカラム名
	 * @param  string $value	一致させたい値
	 * @param  string $operator	使用する演算子(デフォルトは'=')
	 * @return array 条件にマッチした行をインスタンス化した要素の配列
	 */
	public static function findBy($column, $value, $operator = '=') {
		$table_name = (new static())->_getTableName();
		$query = DB::select()
					->from($table_name)
					->where($column, $operator, $value)
					->as_object(get_called_class());

		$ret = $query->execute();

		return static::after_find($ret->as_array());
	}

	/**
	 * LIKE演算子を利用してfindByを行う
	 * @param	string	$column	検索に使用するカラム名
	 * @param	string	$value	部分一致させたい値
	 * @see findBy
	 * @return array 条件にマッチした行をインスタンス化した要素の配列
	 */
	public static function findLike($column, $value) {
		return static::findBy($column, "%$value%", 'LIKE');
	}

	/**
	 * FuelPHPのModel_Crudから持ってきて、このクラス用に改造しました。
	 * http://fuelphp.jp/docs/1.7/classes/model_crud/methods.html#/method_count
	 *
	 * Count all of the rows in the table.
	 *
	 * @param   string  Column to count by
	 * @param   bool    Whether to count only distinct rows (by column)
	 * @param   array   Query where clause(s)
	 * @param   string  Column to group by
	 * @return  int     The number of rows OR false
	 */
	public static function count($column = null, $distinct = true, $where = array(), $group_by = null) {
		// カラムの指定がなければ主キーを使用する
		if(is_null($column)) $column = static::$primaryKey;

		$table_name = (new static())->_getTableName();

		$distinct_token = ($distinct ? 'DISTINCT ' : '');
		$count_token = 'COUNT('.$distinct_token.$column.') AS count_result';
		$count_sql = \DB::expr($count_token);

		$query = \DB::select($count_sql)->from($table_name);

		if ( ! empty($where))
		{
			//is_array($where) or $where = array($where);
			if ( ! is_array($where) and ($where instanceof \Closure) === false)
			{
				throw new \FuelException(get_called_class().'::count where statement must be an array or a closure.');
			}
			$query = call_user_func_array(array($query, 'where'), $where);
		}

		if ( ! empty($group_by))
		{
			$result = $query->select($group_by)->group_by($group_by)->execute()->as_array();
			$counts = array();
			foreach ($result as $res)
			{
				$counts[$res[$group_by]] = $res['count_result'];
			}

			return $counts;
		}

		$count = $query->execute()->get('count_result');

		if ($count === null)
		{
			return false;
		}

		return (int) $count;
	}

	// =======================================
	// トランザクション
	// =======================================
	/**
	 * INSERT, UPDATE, INSERT or UPDATE, DELETEを同一トランザクション配下で行う
	 * NOTE: 同一アクション(insertやupdate)の中の順序は指定できるが、アクション自体の実行順は指定できないことに注意。
	 *       必ずinsert(), update(), save(), delete()の順でモデルが存在するだけ実行される。
	 * @param  Model_Base[] $inserts insert()を呼び出したいModel_Baseのインスタンスの配列 
	 * @param  Model_Base[] $updates update()を呼び出したいModel_Baseのインスタンスの配列
	 * @param  Model_Base[] $saves   save()を呼び出したいModel_Baseのインスタンスの配列
	 * @param  Model_Base[] $deletes delete()を呼び出したいModel_Baseのインスタンスの配列
	 * @return bool 全て成功すればtrue, どれか１つ以上が失敗したらfalse
	 * @throws Database_Exception
	 */
	public static function actionInTransaction(array $inserts, array $updates = array(), array $saves = array(), array $deletes = array()) {
		$result = self::transactionDo(function() use ($inserts, $updates, $saves, $deletes) {
			foreach($inserts as $insert_model) {
				$insert_model->insert();
			}
			foreach($updates as $update_model) {
				$update_model->update();
			}
			foreach($saves as $save_model) {
				$save_model->save();
			}
			foreach($deletes as $delete_model) {
				$delete_model->delete();
			}

			return true;
		});

		return $result;
	}

	// =======================================
	// フック一覧(protected)
	// =======================================

	/**
	 * バリデーションを行う実体部。ここをオーバーライドすれば任意のバリデーションを書ける
	 * @return boolean
	 */
	protected function runValidate() {
		return true;
	}

	/**
	 * 挿入/更新処理の直前に実行できるフック
	 * insert, update, saveメソッドでコールされる。
	 * @param  mixed $query `\Database_Query_Builder_Insert`か`\Database_Query_Builder_Update`
	 * @return void
	 */
	protected function before_save() {}

	/**
	 * 挿入処理の直前に実行できるフック
	 * insertメソッドでコールされる。
	 * @param  \Database_Query $query クエリビルダのインスタンス
	 * @return void
	 */
	protected function before_insert() {}

	/**
	 * 更新処理の直前に実行できるフック
	 * updateメソッドでコールされる。
	 * @param  \Database_Query $query クエリビルダのインスタンス
	 * @return void
	 */
	protected function before_update() {}

	/**
	 * 削除処理の直前に実行できるフック
	 * deleteメソッドでコールされる。
	 * @param  \Database_Query $query クエリビルダのインスタンス
	 * @return void
	 */
	protected function before_delete() {}

	/**
	 * バリデーション処理の直前に実行できるフック
	 * validateメソッドでコールされる。
	 * @return void
	 */
	protected function before_validate() {}

	/**
	 * find処理の直後に実行できるフック  
	 * find, findBy, findLikeでコールされる。
	 * このメソッドの戻り値が、findメソッドの戻り値として使用されるので扱いに要注意。
	 * このメソッドだけstaticなので要注意。
	 * @param  mixed $record インスタンスの配列
	 * @return array
	 */
	protected static function after_find($record) { return $record; }

	/**
	 * 保存処理の直後に実行できるフック
	 * insert, update, saveメソッドでコールされる。
	 * @param  boolean $success 保存に成功したらtrue
	 * @return void
	 */
	protected function after_save($success) {}

	/**
	 * 挿入処理の直後に実行できるフック
	 * insertメソッドでコールされる。
	 * @param  boolean $success 挿入に成功したらtrue
	 * @return void
	 */
	protected function after_insert($success) {}

	/**
	 * 更新処理の直後に実行できるフック
	 * updateメソッドでコールされる。
	 * @param  boolean $success 更新に成功したらtrue
	 * @return void
	 */
	protected function after_update($success) {}

	/**
	 * 削除処理の直後に実行できるフック
	 * deleteメソッドでコールされる。
	 * @param  boolean $success 削除に成功したらtrue
	 * @return void
	 */
	protected function after_delete($success) {}

	/**
	 * バリデーション処理の直後に実行できるフック
	 * validateメソッドでコールされる。
	 * @param  boolean $success バリデーションに通過したらtrue
	 * @return void
	 */
	protected function after_validate($success) {}

	/**
	 * フックをコールするためのメソッド
	 * @param  string $hook_name 実行するフック名
	 * @param  mixed  $args      フックに渡す引数
	 * @return mixed フックの戻り値
	 */
	protected function hook($hook_name/*, $args... */) {
		$args = array_slice(func_get_args(), 1);
		return call_user_func_array(array($this, $hook_name), $args);
	}

	/**
	 * トランザクションを張りその中で処理を実行する
	 * @todo サンプルがショボすぎるのでもうちょい実用性のあるサンプルを書く
	 *
	 * ### sample
	 * ```php
	 * protected function executeInTransaction($query) {
	 * 	return self::transactionDo(function($query) {
	 * 		return $query->execute();
	 * 	}, $query);
	 * }
	 * ```
	 *
	 * @param Clusure $callback トランザクション内で実行する処理
	 * @param mixed   $params   コールバック関数に渡す引数を可変長で受け取る
	 * @return mixed コールバック関数の戻り値、途中でcatchされたらfalse
	 */
	protected static function transactionDo($callback/*, $params... */) {
		$params = array_slice(func_get_args(), 1);

		try {
			DB::start_transaction();
			$ret = call_user_func_array($callback, $params);
			DB::commit_transaction();

		} catch(Database_Exception $e) {
			DB::rollback_transaction();
			throw new Database_Exception($e);
			$ret = false;
		}

		return $ret;
	}

	/**
	 * モデルのクラス名からテーブル名を取得する
	 * クラス上部の説明にある命名規則を参照。
	 * 
	 * @return string クラス名を小文字かつ複数形にしたテーブル名
	 */
	protected static function _getTableName() {
		$lower_class_name = strtolower(get_called_class());
		$model_removed = Str::sub($lower_class_name, strlen('model_'));
		return Inflector::pluralize($model_removed);
	}
}
