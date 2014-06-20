<?php

namespace Fuel\Migrations;

class Create_users
{
	public function up()
	{
		\DBUtil::create_table('users', array(
			'id' 				=> array('constraint' => 11,  'type' => 'int', 'auto_increment' => true, 'unsigned' => true),
			'company_id' 		=> array('constraint' => 11,  'type' => 'int', 'unsigned' => true),
			'uid' 				=> array('constraint' => 128, 'type' => 'char'),
			'next_engine_id' 	=> array('constraint' => 128, 'type' => 'char'),
			'email' 			=> array('constraint' => 255, 'type' => 'varchar'),
			'access_token' 		=> array('constraint' => 128, 'type' => 'char', 'null' => true),
			'refresh_token' 	=> array('constraint' => 128, 'type' => 'char', 'null' => true),
			'updated_at' 		=> array('type' => 'timestamp'),
			'created_at' 		=> array('type' => 'timestamp'),

		), array('id'), true, 'InnoDB');

		\DBUtil::add_foreign_key('users', array(
			'constraint' => 11,
			'key' => 'company_id',
			'reference' => array(
				'table' => 'companies',
				'column' => 'id'
			)
		));
	}

	public function down()
	{
		\DBUtil::drop_table('users');
	}
}