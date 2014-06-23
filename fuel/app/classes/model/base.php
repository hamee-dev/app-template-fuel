<?php

/**
 * アプリのベースとなるモデルクラス。  
 * 全てのモデルクラスの基点はこのクラスとなる。
 */
class Model_User extends \Model_Crud
{
	protected static $_table_name = null;
	protected static $_properties = array();

	protected static $_created_at = 'created_at';
	protected static $_updated_at = 'updated_at';
	protected static $_mysql_timestamp = true;
}
