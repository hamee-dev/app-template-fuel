<?php

namespace Fuel\Migrations;

class Add_access_token_end_date_to_users
{
	public function up()
	{
		\DBUtil::add_fields('users', array(
			'access_token_end_date' => array(
				'type' => 'timestamp',
				'after' => 'access_token',
				'default' => \DB::expr('CURRENT_TIMESTAMP')
			),
		));
	}

	public function down()
	{
		\DBUtil::drop_fields('users', 'access_token_end_date');
	}
}
