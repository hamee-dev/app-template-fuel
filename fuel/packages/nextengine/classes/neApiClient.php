<?php
/**
 * Nextengine API SDK(http://api.next-e.jp/).
 *
 * @since 2013/10/10
 * @copyright Hamee Corp. All Rights Reserved.
 *
*/

class neApiClient {
	////////////////////////////////////////////////////////////////////////////
	// 利用するサーバーのURLのスキーム＋ホスト名の定義
	////////////////////////////////////////////////////////////////////////////
	const API_SERVER_HOST   = 'https://api.next-engine.org' ;
	const NE_SERVER_HOST    = 'https://base.next-engine.org' ;

	////////////////////////////////////////////////////////////////////////////
	// 認証に用いるURLのパスを定義
	////////////////////////////////////////////////////////////////////////////
	const PATH_LOGIN	= '/users/sign_in/' ;	// NEログイン
	const PATH_OAUTH	= '/api_neauth/' ;		// API認証

	////////////////////////////////////////////////////////////////////////////
	// APIのレスポンスの処理結果ステータスの定義
	////////////////////////////////////////////////////////////////////////////
	const RESULT_SUCCESS	= 'success' ;	// 成功
	const RESULT_ERROR		= 'error' ;		// 失敗
	const RESULT_REDIRECT	= 'redirect';	// 要リダイレクト

	////////////////////////////////////////////////////////////////////////////
	// APIの接続情報(__constructのヘッダーコメントを参照して下さい)
	////////////////////////////////////////////////////////////////////////////
	public		$_access_token	= NULL ;
	public		$_refresh_token	= NULL ;

	///////////////////////////////////////////////////////
	// SDK内部でAPIを利用する為に使うメンバ変数
	///////////////////////////////////////////////////////
	// 認証パラメータ
	protected	$_client_id		= NULL ;
	protected	$_client_secret	= NULL ;
	protected	$_redirect_uri	= NULL ;
	protected	$_uid			= NULL ;
	protected	$_state			= NULL ;
	// cUrlハンドル
	protected	$_curl			= NULL ;

	/**
	* インスタンス生成時、実行環境に合わせた値を引数に指定して下さい。
	* 
	* redirect_uriの説明：
	*   まだ認証していないユーザーがアクセスした場合(ネクストエンジンログインが必要な場合)、
	*   本SDKが自動的にネクストエンジンのログイン画面にリダイレクトします（ユーザーには認証画面が表示される）。
	*   ユーザーが認証した後、ネクストエンジンサーバーから認証情報と共にアプリケーションサーバーに
	*   リダイレクトします。その際のアプリケーションサーバーのリダイレクト先uriです。
	*
	* redirect_uriの省略又はNULL指定について：
	*   通常のWebアプリケーションの場合は、必ず指定して下さい。
	*   NULLにするのは、一度Webアプリケーションで認証した後、バッチ等で非同期にAPIを実行する場合のみです。
	*   NULLにし認証の有効期限が切れた場合(resultがRESULT_REDIRECT)、SDK内部で自動的にリダイレクトせず
	*   結果はredirectのまま正常終了しません（認証の有効期限が切れた場合は、再度Web経由で認証の必要あり）。
	*
	* access_tokenとrefresh_tokenの説明：
	*   バッチ等で非同期にAPIを実行する場合のみ、認証した状態を保持する為に必要です。
	*
	* access_tokenとrefresh_tokenの省略(NULL指定)について：
	*   通常のWebアプリケーションの場合は、省略して下さい。
	*   指定するのは、一度Webアプリケーションで認証した後、バッチ等で非同期にAPIを実行する場合のみです。
	*   指定する値は、最後にapiExecute又はneLogin呼び出し後の同名のメンバ変数の値です。
	*   この値を初回ログイン時などにDBに保存しておき、バッチではその値を元に処理を実行することを想定しています。
	*	注意：この値はユーザー毎(uid毎)に管理する必要があります。別のユーザーの値を指定してSDKを実行すると
	*		  他ユーザーの情報にアクセスしてしまうため、厳重にご注意をお願いします。
	*
	* @param	string	$client_id		クライアントID。
	* @param	string	$client_secret	クライアントシークレット。
	* @param	string	$redirect_uri	ヘッダーコメント参照。
	* @param	string	$access_token	同上。
	* @param	string	$refresh_token	同上。
	* @return	void
	*/
	public function __construct($client_id, $client_secret, $redirect_uri = NULL, $access_token = NULL, $refresh_token = NULL) {
		$this->_curl = curl_init() ;
		if( $this->_curl === false ) {
			throw new Exception('システムエラーが発生しました。') ;
		}
		$this->_client_id		= $client_id ;
		$this->_client_secret	= $client_secret ;
		$this->_redirect_uri	= $redirect_uri ;
		$this->_access_token	= $access_token ;
		$this->_refresh_token	= $refresh_token ;

		curl_setopt($this->_curl, CURLOPT_HEADER,			false) ;
		curl_setopt($this->_curl, CURLOPT_ENCODING,			'Accept-Encoding: gzip,deflate') ;
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER,	true) ;
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST,	2) ;
		curl_setopt($this->_curl, CURLOPT_TIMEOUT,			3600) ;
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER,	true) ;
		if(!is_null($redirect_uri)) {
			curl_setopt($this->_curl, CURLOPT_REFERER,		'https://'.parse_url($redirect_uri, PHP_URL_HOST)) ;
		}

		// 次のエラーが出てcURLの実行に失敗する場合、cURLの提供元からcacert.pemを取得し、
		// アプリサーバーに設置の上、パスを設定してコメントアウトを解除してください。
		// SSL certificate problem, verify that the CA cert is OK. Details: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed
		//curl_setopt($this->_curl, CURLOPT_CAINFO,			'./cacert.pem') ;
	}

	/**
	* ネクストエンジンログインを実施し、かつAPIを実行し、結果を返します。
	*
	* @param	string	$path			呼び出すAPIのパスです。/から指定して下さい。
	* @param	array	$api_params		呼び出すAPIの必要に応じてパラメータ(連想配列)です。
	*									パラメータが不要な場合、省略又はNULLを指定して下さい。
	* @param	string	$redirect_uri	インスタンスを作成した後、リダイレクト先を変更したい
	*									場合のみ設定して下さい。
	* @return	array  実行結果。内容は呼び出したAPIにより異なります。
	*/
	public function apiExecute($path, $api_params = array(), $redirect_uri = NULL) {
		if( !is_null($redirect_uri) ) {
			$this->_redirect_uri = $redirect_uri ;
		}

		// access_tokenが未発行の場合、メンバ変数に設定
		if( is_null($this->_access_token) ) {
			// uid及びstateをメンバ変数に設定
			$this->setUidAndState() ;

			// uid及びstateを元にaccess_tokenを発行
			$response = $this->setAccessToken() ;
			if( $response['result'] !== self::RESULT_SUCCESS ) {
				return($response) ;
			}
		}

		$api_params['access_token'] = $this->_access_token ;
		if( isset($this->_refresh_token) ) {
			$api_params['refresh_token'] = $this->_refresh_token ;
		}

		// APIを実行して処理結果を返す
		$response = $this->post(self::API_SERVER_HOST.$path, $api_params) ;

		if( isset($response['access_token']) ) {
			$this->_access_token = $response['access_token'] ;
		}
		if( isset($response['refresh_token']) ) {
			$this->_refresh_token = $response['refresh_token'] ;
		}

		// リダイレクトの可能性があるのでチェックする(成功・失敗に関わらず結果を返して終了)
		$this->responseCheck($response) ;
		return($response) ;
	}

	/**
	* ネクストエンジンログインが不要なAPIを実行します。
	* 
	* @param	string	$path			呼び出すAPIのパスです。/から指定して下さい。
	* @param	array	$api_params		呼び出すAPIの必要に応じてパラメータ(連想配列)です。
	*									パラメータが不要な場合、省略又はNULLを指定して下さい。
	*
	* @return	array  実行結果。内容は呼び出したAPIにより異なります。
	*/
	public function apiExecuteNoRequiredLogin($path, $api_params = array()) {
		$api_params['client_id'] = $this->_client_id ;
		$api_params['client_secret'] = $this->_client_secret ;

		$response = $this->post(self::API_SERVER_HOST.$path, $api_params) ;
		return($response) ;
	}

	/**
	* ネクストエンジンログインのみ実行します。
	* 既にログインしている場合、ログイン後の基本情報を返却します。
	* まだログインしていない場合、ネクストエンジンログイン画面にリダイレクトされ、
	* 正しくログインした場合、$redirect_uriにリダイレクトされます。
	* リダイレクト先で、再度neLoginを呼ぶ事で、ログイン後の基本情報を返却します。
	* 
	* @param	string	$redirect_uri	インスタンスを作成した後、リダイレクト先を変更したい
	*									場合のみ設定して下さい。
	* @return	array  NE APIのログイン後の基本情報。
	*/
	public function neLogin($redirect_uri = NULL) {
		if( !is_null($redirect_uri) ) {
			$this->_redirect_uri = $redirect_uri ;
		}

		// uid及びstateをメンバ変数に設定
		$this->setUidAndState() ;

		$params = array('uid' => $this->_uid, 'state' => $this->_state) ;
		$response = $this->post(self::API_SERVER_HOST.self::PATH_OAUTH, $params) ;

		// リダイレクトの可能性があるのでチェックする(成功・失敗に関わらず結果を返して終了)
		$this->responseCheck($response) ;

		return($response) ;
	}

	///////////////////////////////////////////////////////
	// 以下は全てSDKの内部処理用のメソッドです
	///////////////////////////////////////////////////////
	public function __destruct() {
		curl_close($this->_curl);
	}

	/**
	* メンバ変数にuidとstateを設定します。
	*
	* 1.NEからアプリを起動した場合。
	*	uidとstateがGETパラメータに渡ってくる為、メンバ変数に設定します。
	* 2.直接アプリを起動した場合。
	*	uidとstateがGETパラメータに渡ってこない為、NEに認証に行きます(NEサーバーへリダイレクト)。
	*	以下のようにユーザーの認証が終わると本サーバーにNEサーバーからリダイレクトされます。
	*	2.1.起動したユーザーが既にNEログイン済みの場合。
	*		認証画面を表示せずに$redirect_uriにリダイレクトされます。
	*	2.2.起動したユーザーがまだNEログインしていない場合。
	*		認証画面を表示して$redirect_uriにリダイレクトされます。
	* 
	* @return	void	
	*/
	protected function setUidAndState() {
		// ネクストエンジンにログインしていて、ネクストエンジンから起動された場合
		// uid及びstateパラメータが渡ってくる。
		if( isset($_GET['uid']) ) {
			$this->_uid = $_GET['uid'] ;
		}
		if( isset($_GET['state']) ) {
			$this->_state = $_GET['state'] ;
		}

		// uidが未発行の場合、NEログイン画面へリダイレクトする
		if( !isset($this->_uid) || !isset($this->_state) ) {
			$this->redirectNeLogin() ;
		}
	}

	/**
	* メンバ変数にaccess_token(とあればrefresh_token)を設定します。
	*
	* @return	array  access_token発行処理の実行結果。
	*/
	protected function setAccessToken() {
		$params = array('uid' => $this->_uid, 'state' => $this->_state) ;
		$response = $this->post(self::API_SERVER_HOST.self::PATH_OAUTH, $params) ;
		if( !$this->responseCheck($response) ) {
			return($response) ;
		}

		$this->_access_token = $response['access_token'] ;
		if( isset($response['refresh_token']) ) {
			$this->_refresh_token = $response['refresh_token'] ;
		}
		return($response) ;
	}


	protected function responseCheck($response) {
		switch($response['result']) {
		case self::RESULT_ERROR : 		//エラー
			return(false) ;
		case self::RESULT_REDIRECT :	// リダイレクト
			// リダイレクトしない場合は、エラーと同じ
			if( is_null($this->_redirect_uri) ) {
				return(false) ;
			}
			// リダイレクトする
			else {
				$this->redirectNeLogin() ;
			}
		case self::RESULT_SUCCESS : 	// 成功
			return(true) ;
		default :
			throw new Exception('SDKで例外が発生しました。クライアントID・シークレットや指定したパスが正しいか確認して下さい') ;
		}
	}

	protected function redirectNeLogin() {
		$params = array() ;
		$params['client_id'] = $this->_client_id ;
		$params['client_secret'] = $this->_client_secret ;
		$params['redirect_uri'] = $this->_redirect_uri ;

		$url = self::NE_SERVER_HOST.self::PATH_LOGIN.'?'.$this->getUrlParams($params) ;
		header('HTTP/1.0 302 OAuth Redirection');
		header('Location: '.$url);
		exit ;
	}

	protected function post($url, $params) {
		curl_setopt($this->_curl, CURLOPT_URL,				$url) ;
		curl_setopt($this->_curl, CURLOPT_POSTFIELDS,		$this->getUrlParams($params)) ;
		curl_setopt($this->_curl, CURLOPT_POST,				true) ;
		
		$response = curl_exec($this->_curl) ;
		if( $response === FALSE ) {
			throw new Exception('システムエラーが発生しました(curlの実行に失敗['. curl_error($this->_curl) .'])。') ;
		}
		return(json_decode($response, true)) ;
	}

	protected function getUrlParams($params) {
		if( count($params) <= 0 ) {
			return('') ;
		}
		$get_param = '' ;
		foreach( $params as $k => $v ) {
			$get_param .= "&{$k}=" . urlencode($v) ;
		}
		return(substr($get_param, 1)) ;
	}
}
?>
