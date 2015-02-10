<?php
/**
 * @author Shingo Inoue<inoue.shingo@hamee.co.jp>
 */

namespace Base;

/**
 * 認証処理のコントローラの基底となるクラス
 */
abstract class Controller_Auth extends Controller_Base
{
	/**
	 * ネクストエンジンAPIクライアントのインスタンスを保持する
	 * @var \Nextengine\Api\Client
	 */
	protected static $client;

	/**
	 * ネクストエンジンAPIクライアントのインスタンスを生成する
	 * 
	 * このクラスを継承したクラスで_initを定義したい場合には、"必ず"parent::_init()をコールして下さい
	 * 
	 * @return void
	 */
	public static function _init()
	{
		parent::_init();

		// NOTE: 認証画面ではコンストラクタに何も渡せない（login時には何も渡せるものがないので、ナシで統一）
		self::$client = new \Nextengine\Api\Client_Router();
	}

	// ------------------------------------------------------------------------
	//  ▼ アクションメソッド ▼
	// ------------------------------------------------------------------------

	/**
	 * route: [GET] /auth/login
	 * 
	 * ネクストエンジンAPIクライアントクラスの`neLogin`メソッドを呼び出す。  
	 * neLoginの認証処理につきましては、SDKのドキュメントを御覧下さい。
	 * http://api.next-e.jp/sdk.php#php
	 * 
	 * @return void
	 */
	public function get_login()
	{
		self::$client->neLogin();
	}

	/**
	 * route: [GET] /auth/logout
	 * 
	 * 現在のセッションを破棄する
	 * リダイレクト等の後処理は継承クラスに委ねる
	 * 
	 * @return void
	 */
	public function get_logout()
	{
		\Session::destroy();
	}

	/**
	 * route: [GET] /auth/callback
	 * 
	 * ネクストエンジンAPIの認証が済むとリダイレクトされるメソッドです。
	 * セッションやGETパラメータの値を見て、認証済みのデータをDBとセッションに保存します。
	 * 
	 * @return void
	 */
	public function get_callback()
	{
		$session_key_company = \Config::get('session.keys.ACCOUNT_COMPANY');
		$session_key_user    = \Config::get('session.keys.ACCOUNT_USER');

		// NOTE: 可読性と関数呼び出しのオーバーヘッド軽減のため、結果を変数にキャッシュ
		$session_user = \Session::get($session_key_user);
		$uid          = \Input::get('uid');
		$state        = \Input::get('state');

		// セッションもURLにも何もない = 通常操作では起こりえない非正規ルートなので再認証させる
		if(is_null($uid) && is_null($state) && is_null($session_user)) {
			\Response::redirect('/auth/login');
		}

		// NOTE: 企業情報を取得し、main_function_id(UNIQUE)でSELECTをかける。
		//       見つかればそのデータを上書きして更新、見つからなければ新規インスタンスに値をセットして挿入する
		// NOTE: カラム名はmain_function_idだが、取得した企業情報のフィールドでは`company_id`なことに注意。
		$company_info = $this->_fetch_company_info();
		$companies    = \Model_Company::findBy('main_function_id', $company_info['company_id']);
		$company      = ((count($companies) > 0) ? $companies[0] : new \Model_Company());

		$company = $this->_create_company($company, $company_info);
		$company->save();


		// NOTE: ユーザ情報を取得し、uid(UNIQUE)でSELECTをかける。
		//       見つかればそのデータを上書きして更新、見つからなければ新規インスタンスに値をセットして挿入する
		$user_info = $this->_fetch_user_info();
		$users     = \Model_User::findBy('uid', $user_info['uid']);
		$user      = ((count($users) > 0) ? $users[0] : new \Model_User());

		$user = $this->_create_user($user, $user_info, $company->id);
		$user->save();

		// セッションにログインユーザの情報をセット
		\Session::set($session_key_company, $company);
		\Session::set($session_key_user, $user);
		\Session::set('company_app_header', $company_info['company_app_header']);
	}


	// ------------------------------------------------------------------------
	//  ▼ ユーティリティ ▼
	// ------------------------------------------------------------------------

	/**
	 * APIから取得した情報を元にCompanyモデルを作成し返却する
	 * 
	 * インスタンスは、既にDBに存在するデータから取得するか、存在しない場合newされたものが渡される
	 * ログイン処理の度に毎回更新すべき情報を全てセットすること。
	 * 
	 * @param  \Model_Company $company      （DBから取得した or newした）企業モデルのインスタンス
	 * @param  array          $company_info ログイン企業の情報（連想配列）
	 * @return \Model_Company プロパティに値をセットしたインスタンス
	 */
	protected function _create_company(\Model_Company $company, array $company_info)
	{
		$company->platform_id      = $company_info['company_ne_id'];
		$company->main_function_id = $company_info['company_id'];

		return $company;
	}

	/**
	 * APIから取得した情報を元にUserモデルを作成し返却する
	 * 
	 * インスタンスは、既にDBに存在するデータから取得するか、存在しない場合newされたものが渡される
	 * ログイン処理の度に毎回更新すべき情報を全てセットすること。
	 * 
	 * @param  Model_User $user       （DBから取得した or newした）ユーザモデルのインスタンス
	 * @param  array      $user_info  ログインユーザの情報（連想配列）
	 * @param  int        $company_id 所属している企業ID
	 * @return Model_User プロパティに値をセットしたインスタンス
	 */
	protected function _create_user(\Model_User $user, array $user_info, $company_id)
	{
		$user->company_id     = $company_id;
		$user->uid            = $user_info['uid'];
		$user->next_engine_id = $user_info['pic_ne_id'];
		$user->email          = $user_info['pic_mail_address'];
		$user->access_token   = static::$client->_access_token;
		$user->refresh_token  = static::$client->_refresh_token;

		return $user;
	}

	/**
	 * NE APIからユーザ情報を取得するユーティリティ
	 * 
	 * @return array 企業情報の連想配列。参照：http://api.next-e.jp/fields_login.php#company
	 */
	protected function _fetch_company_info()
	{
		$company_info = self::$client->apiExecute('/api_v1_login_company/info');
		$company_info = $company_info['data'][0];

		return $company_info;
	}

	/**
	 * NE APIからユーザ情報を取得するユーティリティ
	 * 
	 * @return array ユーザ情報の連想配列。参照：http://api.next-e.jp/fields_login.php#pic
	 */
	protected function _fetch_user_info()
	{
		$user_info = self::$client->apiExecute('/api_v1_login_user/info');
		$user_info = $user_info['data'][0];

		return $user_info;
	}
}
