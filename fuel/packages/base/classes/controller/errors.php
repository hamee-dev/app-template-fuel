<?php
/**
 * @author Shingo Inoue<inoue.shingo@hamee.co.jp>
 */

namespace Base;

/**
 * エラー画面のレンダリングを行うクラス
 */
abstract class Controller_Errors extends Controller_Base
{
	/**
	 * このコントローラ配下の画面で使用するテンプレートファイル名(.phpは除く)
	 * @var string
	 */
	public $template = 'template-error';

	/**
	 * エラー画面を描画する
	 * @return void
	 */
	public function get_index()
	{
		$this->template->title = 'Error &raquo; Index';
		$this->template->content = \View::forge('errors/index');
	}

	/**
	 * メンテナンス中のエラー画面を描画する
	 * @return void
	 */
	public function get_maintenance()
	{
		$this->template->title = 'Error &raquo; Maintenance';
		$this->template->content = \View::forge('errors/maintenance');
	}

	/**
	 * システムが混み合っている場合のエラー画面を描画する
	 * @return void
	 */
	public function get_congestion()
	{
		$this->template->title = 'Error &raquo; Congestion';
		$this->template->content = \View::forge('errors/congestion');
	}
}
