<?php

namespace Nextengine\Api;

require_once __DIR__.DS.'..'.DS.'..'.DS.'neApiClient.php';

/**
 * ネクストエンジンAPIからのレスポンスによって発生する例外クラス
 * 発生する例外やその処理については継承クラスとドキュメントを参照。
 * ドキュメント：http://api.next-e.jp/message.php
 * @see Nextengine\Api\Client_Router
 * @see Nextengine\Api\Client_Batch
 */
class NextengineApiException extends \FuelException {
	/**
	 * @param string $message エラーメッセージ(APIからのエラーメッセージをそのまま投げる)
	 * @param string $code    エラーコード(APIからのエラーコードをそのまま投げる)
	 */
	public function __construct($message, $code) {
		// NOTE: 先にcodeプロパティを定義してしまえば、
		//       親クラスのコンストラクタでcodeプロパティが上書きされることはない
		$this->code = $code;
		parent::__construct($message);
	}
}

/**
 * ネクストエンジンAPIクライアント
 * 
 * 例外による処理の振り分けを実装しているのは継承クラスなので、そちらを参照。
 * @see Nextengine\Api\Client_Router
 * @see Nextengine\Api\Client_Batch
 */
class Client extends \neApiClient
{
	/**
	 * デフォルトの設定値
	 * @var array
	 */
	protected static $_defaults = array();

	/**
	 * ユーザオブジェクトを格納する
	 * @var array
	 */
	protected $user = null;

	/**
	 * 設定をロードし、_defaultsプロパティに格納しておく
	 * @return void
	 */
	public static function _init()
	{
		self::$_defaults = \Config::load('nextengine', true);
	}

	/**
	 * FuelPHP式（ファクトリメソッド）のコンストラクタ
	 * 内部は単にコンストラクタを呼ぶだけ
	 *
	 * @param  array      $config 設定値（この値が優先される）
	 * @return Nextengine\Api\Client
	 */
	public static function forge(array $config = array())
	{
		$class = new static($config);
		return $class;
	}

	/**
	 * コンストラクタに与えられた設定でデフォルト設定を上書きし、接続に必要な情報を格納する
	 * @param  array      $config 設定値（この値が優先される）
	 * @return Nextengine\Api\Client
	 */
	public function __construct(array $config = array())
	{
		$config = \Arr::merge(self::$_defaults, $config);

		parent::__construct(
			$config['client_id'],
			$config['client_secret'],
			$config['redirect_uri']);
	}

	/**
	 * ネクストエンジンAPIを叩く
	 * 
	 * ### 親クラスからの拡張点
	 * レスポンスに含まれるresultフィールドの値がsuccessでないなら、例外を投げるという処理を追加した
	 *
	 * @throws NextengineApiException
	 */
	public function apiExecute($path, $api_params = array(), $redirect_uri = NULL) {
		$before_exec_access_token = $this->_access_token;
		$response = parent::apiExecute($path, $api_params, $redirect_uri);

		// NOTE: エラーの種類については→を参照：http://api.next-e.jp/message.php
		//       エラー時の振る舞いについては、継承クラスのfailoverメソッドを参照。
		if($response['result'] !== self::RESULT_SUCCESS) {
			$this->failover($response['code'], $response['message']);
		}

		// APIを叩く前後でアクセストークンが変わっていたら、ユーザモデルを更新してDBに反映、セッションも更新する
		// ただしユーザモデルが格納されていない場合もあるので、その場合はその処理を行わない
		if(!is_null($this->user) && $before_exec_access_token !== $this->_access_token) {
			$this->user->access_token  = $this->_access_token;
			$this->user->refresh_token = $this->_refresh_token;
			$this->user->save();

			$user_key = Config::get('session.keys.ACCOUNT_USER');
			Session::set($user_key, $this->user);
		}

		return $response;
	}

	/**
	 * ユーザ情報をセットする
	 * @param Model_User $user ユーザのインスタンス
	 * @return void
	 */
	public function setUser($user) {
		// クライアントのアクセストークンがNULL以外で、DBの値と違っていたら、DBを更新
		// NOTE: クライアントのプロパティが最も最新、DBの値はその次に新しい。優先すべきはクライアントのプロパティ。
		if(!is_null($this->_access_token) && $user->access_token !== $this->_access_token) {
			$user->access_token = $this->_access_token;
			$user->refresh_token = $this->_refresh_token;
			$user->save();
		}

		// まだAPIを叩いていない状態で、アクセストークンがあるユーザが渡されたら、クライアントのプロパティを更新
		if(is_null($this->_access_token) && !is_null($user->access_token)) {
			$this->_access_token = $user->access_token;
			$this->_refresh_token = $user->refresh_token;
		}

		$this->user = $user;
	}

	/**
	 * ネクストエンジンAPIからエラーが帰っていた場合の処理を行う
	 * @param  string $code    NextengineApiExceptionに渡すエラーコード
	 * @param  string $message NextengineApiExceptionに渡すエラーメッセージ
	 * @return void
	 * @throws NextengineApiException
	 */
	protected function failover($code, $message) {
		// TODO: ログの出力/メール送信
		throw new NextengineApiException($message, $code);
	}
}
