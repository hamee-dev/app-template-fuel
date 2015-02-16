<?php

return array(
	'client_id'		=> null,
	'client_secret' => null,
	'redirect_uri'	=> null,

	'debug' => array(
		// デバッグや通知メールを送るべき開発者
		'developer'		=> array(),

		// デバッグや通知メールを送るべき管理者（開発者の上位互換）
		'administrator'	=> array(),

		'mail_subject' => ''
	)
);
