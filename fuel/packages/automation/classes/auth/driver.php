<?php

namespace Automation\Auth;

abstract class Driver extends \Automation\Driver
{
	/**
	 * 【フック】ユーザがブラウザからログイン処理を行った後のフック  
	 * デフォルトではなにもしないので必要であれば拡張すること。  
	 * このフックが呼び出された後、２つのモデルはsaveされる。  
	 * ログイン直後に独自で挟みたい処理や、アプリ固有で追加したカラムの値の格納などがあれば、ここへ記述する。
	 * NOTE: 初回ログインだけでなく、ログインのたびに呼び出される（DBに存在するか否かを考慮せずINSERT or UPDATEしているため）ことに注意。
	 *       ただし、このフックが呼び出された時点ではまだモデルはsaveされていないので、`$user->isNew()`がtrueなら新規ログインと扱うことができる？？？？？？
	 * @param \Model_Company $company  ログイン処理が行われた企業
	 * @param \Model_User    $user     ログイン処理が行われたユーザ
	 * @return void
	 */
	public function after_login(\Model_Company $company, \Model_User $user) {}
}
