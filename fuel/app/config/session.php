<?php

return array(
	// セッションのドライバとして何を使用するか（cookie, file, db, memcached, redis）
	'driver' => 'cookie',
	'enable_cookie' => true,

	// クッキーをHTTPのみで使用する
	'cookie_http_only' => true,

	// TODO: 説明
	'expire_on_close' => false,
	'expiration_time' => 7200,
	'rotation_time' => 300,

	// セッションのキーを設定値として持つ
	'keys' => array(
	    'ACCOUNT_USER' => 'account.user',
	    'ACCOUNT_COMPANY' => 'account.company',
	)
);
