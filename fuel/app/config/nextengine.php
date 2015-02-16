<?php

return array(
	'client_id' => 'XXXXXXXXXXXXXX',
	'client_secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
	'redirect_uri' => 'https://192.168.33.10/auth/callback',

	'debug' => array(
		// デバッグや通知メールを送るべき開発者
		'developer'		=> array(
			'developer@example.com',
		),

		// デバッグや通知メールを送るべき営業
		'sales'	=> array(
			'sales@example.com',
		),

		'mail_subject' => "[NE-API] NextengineApiException"
	)
);
