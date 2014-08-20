<?php
/**
 * @author Shingo Inoue<inoue.shingo@hamee.co.jp>
 */

namespace Base;

class Model_User extends Model_Base
{
	public $company_id;
	public $uid;
	public $email;
	public $next_engine_id;
	public $access_token  = null;
	public $refresh_token = null;
}
