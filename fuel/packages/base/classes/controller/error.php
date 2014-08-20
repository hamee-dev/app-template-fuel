<?php
/**
 * @author Shingo Inoue<inoue.shingo@hamee.co.jp>
 */

namespace Base;

/**
 * エラー画面のレンダリングを行うクラス
 */
abstract class Controller_Error extends Controller_Base
{
	public $template = 'template-error';

	public function get_index()
	{
		$this->template->title = 'Error &raquo; Index';
		$this->template->content = \View::forge('error/index');
	}

	public function get_maintenance()
	{
		$this->template->title = 'Error &raquo; Maintenance';
		$this->template->content = \View::forge('error/maintenance');
	}

	public function get_congestion()
	{
		$this->template->title = 'Error &raquo; Congestion';
		$this->template->content = \View::forge('error/congestion');
	}
}
