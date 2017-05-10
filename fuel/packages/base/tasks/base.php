<?php

namespace Base;

/**
 * タスクの基底クラス
 *
 * Fatal error のハンドリングを行う。
 * タスクを作る場合はこのクラスを継承し、handle_fatal_errorメソッドを実装してください。
 */
abstract class Runner
{
	public function __construct()
	{
		\Event::register('shutdown', function () {
			$error = error_get_last();
			if (empty($error)) {
				return;
			}
			// メモリー不足によるエラーが起こった場合に備える。
			// エラー処理を行うためにメモリーを増やす。
			ini_set('memory_limit', -1);
			$this->handle_fatal_error($error);
		});
	}

	protected abstract function handle_fatal_error($error);

}
