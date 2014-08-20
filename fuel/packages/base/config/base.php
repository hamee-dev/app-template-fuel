<?php

return array(
	/**
	 * ログファイルに関する設定
	 */
	'log' => array(
		/**
		 * ログファイルを格納するベースとなるパス
		 * fuel/app/以下のディレクトリ名を指定。デフォルトの`logs`だと、
		 * `(fuel/app/)logs(/{:company_id}/Ymd.log)`にログが保存される
		 */
		'base_path'  => 'logs',

		/**
		 * ログに残すレベルの下限を指定。
		 * ここで指定したよりも小さいレベルのログはファイルに残されない。
		 * ここで指定できる定数は、
		 * - DEBUG		(100): Detailed debug information.
		 * - INFO		(200): Interesting events. Examples: User logs in, SQL logs.
		 * - NOTICE		(250): Normal but significant events.
		 * - WARNING	(300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
		 * - ERROR		(400): Runtime errors that do not require immediate action but should typically be logged and monitored.
		 * - CRITICAL	(500): Critical conditions. Example: Application component unavailable, unexpected exception.
		 * - ALERT		(550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
		 * - EMERGENCY	(600): Emergency: system is unusable.
		 * の8つである。（https://github.com/Seldaek/monolog#log-levels）
		 */
		'level' => Base\Logger::DEBUG,

		/**
		 * ファイル名の書式を指定する
		 * "{この設定値をdate関数に与えた結果}.log"がログファイル名となる。
		 * 使える値はdate関数のドキュメントを参照
		 * http://php.net/manual/ja/function.date.php
		 */
		'file_name' => 'Ymd',
	),
);
