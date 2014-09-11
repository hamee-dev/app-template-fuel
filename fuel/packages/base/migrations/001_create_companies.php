<?php

namespace Fuel\Migrations;

class Create_companies
{
	public function up()
	{
		\DBUtil::create_table('companies', array(
			'id' => array(
				'constraint' => 11,
				'type' => 'int',
				'auto_increment' => true,
				'unsigned' => true
			),
			'main_function_id' => array(
				'constraint' => 128,
				'type' => 'char'
			),
			'platform_id' => array(
				'constraint' => 128,
				'type' => 'char'
			),
			'created_at' => array(
				'type' => 'timestamp',
				'default' => \DB::expr('CURRENT_TIMESTAMP')
			),
			'updated_at' => array(
				'type' => 'timestamp',
				'default' => \DB::expr('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')
			),

		), array('id'), true, 'InnoDB');

		\DBUtil::create_index('companies', 'main_function_id', 'unique_main_function_id', 'unique');
	}

	public function down()
	{
		\DBUtil::drop_index('companies', 'unique_main_function_id');
		\DBUtil::drop_table('companies');
	}
}
