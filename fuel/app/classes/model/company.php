<?php

class Model_Company extends Model_Base
{
	protected static $_properties = array(
		'id',
		'main_function_id',
		'platform_id',
		'created_at',
		'updated_at',
	);

	protected static $_table_name = 'companies';

}
