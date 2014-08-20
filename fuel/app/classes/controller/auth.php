<?php

class Controller_Auth extends \Base\Controller_Auth
{
	/**
	 * route: [GET] /auth/logout
	 * 
	 * 現在のセッションを破棄し、リダイレクトを行う
	 * 
	 * @return void
	 */
	public function get_logout()
	{
		parent::get_logout();
		\Response::redirect('/');
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
		parent::get_callback();

		// NOTE: 動作デモを試したら、コメントアウトを解除して任意の場所へリダイレクトさせて下さい。
		//       http://api.next-e.jp/secret/sample-fuelphp/about-sample.php
		// \Response::redirect('/demo/api/find');

		// NOTE: 上記を編集しリダイレクトさせる場合には下記の記述は不要です。消して下さい。
		$this->template->title = 'Authenticate complete!!';
		$this->template->content = "";
	}


	// ------------------------------------------------------------------------
	//  ▼ ユーティリティ ▼
	// ------------------------------------------------------------------------

	/**
	 * APIから取得した情報を元にCompanyモデルを作成し返却する
	 * 既にDBに企業データが存在している場合は、それを取得して返す。
	 * 
	 * @param  array $company_info ログイン企業の情報（連想配列）
	 * @return Model_Company プロパティに値をセットしたインスタンス
	 */
	protected function _create_company(array $company_info)
	{
		$company = parent::_create_company($company_info);

		// NOTE: ここに任意のパラメータを入れ込む処理を記述できます。

		return $company;
	}

	/**
	 * APIから取得した情報を元にUserモデルを作成し返却する
	 * 
	 * @param  array $user_info  ログインユーザの情報（連想配列）
	 * @param  int   $company_id 所属している企業ID
	 * @return Model_User プロパティに値をセットしたインスタンス
	 */
	protected function _create_user(array $user_info, $company_id)
	{
		$user = parent::_create_user($user_info, $company_id);

		// NOTE: ここに任意のパラメータを入れ込む処理を記述できます。

		return $user;
	}
}
