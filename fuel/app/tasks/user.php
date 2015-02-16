<?php

namespace Fuel\Tasks;

class User
{
	/**
	 * This method gets ran when a valid method name is not used in the command.
	 *
	 * Usage (from command line):
	 *
	 * php oil r user
	 *
	 * @return string
	 */
	public function run($args = null)
	{
		echo "\n===========================================";
		echo "\nRunning DEFAULT task [User:Run]";
		echo "\n-------------------------------------------\n\n";

		/***************************
		 Put in TASK DETAILS HERE
		 **************************/
	}

	/**
	 * ユーザのアクセストークンをリフレッシュするタスク
	 * user_idを与えるとそのIDのユーザのアクセストークンのみリフレッシュする。
	 *
	 * ## Usage (from command line):
	 * `php oil r user:refresh [user_id]`
	 *
	 * @return string
	 */
	public function refresh($user_id = null)
	{
		if(is_null($user_id)) {
			$users = \Model_User::findAll();
		} else {
			// NOTE: 1件でも全件でもforeachで回すためにあえて配列に格納している
			$users = array(\Model_User::find($user_id));
		}

		foreach($users as $user) {
			// NOTE: $usersがnullならそもそもプロパティにアクセスできないので別if文にしている
			if(is_null($users)) continue;
			if(is_null($user->access_token) || is_null($user->refresh_token)) continue;

			$this->_refresh($user);
		}
	}

	/**
	 * トークンのリフレッシュを行う実体部。
	 * 
	 * @param Model_User $user トークンを更新したいユーザオブジェクト
	 * @return boolean 更新に成功したらtrue、更新が必要なかった場合もtrue
	 */
	private function _refresh(\Model_User $user)
	{
		$client = new \Nextengine\Api\Client();
		$client->setUser($user);

		$client->apiExecute('/api_v1_login_user/info');
	}
}
