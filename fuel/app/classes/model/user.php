<?php

class Model_User extends Model_Base
{
	protected static $_properties = array(
		'id',
		'company_id',
		'uid',
		'next_engine_id',
		'email',
		'access_token',
		'refresh_token',
		'created_at',
		'updated_at',
	);

	protected static $_table_name = 'users';

}
