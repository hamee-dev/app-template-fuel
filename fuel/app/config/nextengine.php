<?php

return array(
	'client_id' => '2zG7d5MjXPh4m8',
	'client_secret' => 'FTNubmlpyAgE5e3BnqWt6IHJ18voxVkMS9Yh4Zjc',
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
