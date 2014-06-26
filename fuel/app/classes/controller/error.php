<?php

class Controller_Error extends Controller_Template
{

	public function action_index()
	{
		$this->template->title = 'Error &raquo; Index';
		$this->template->content = View::forge('error/index', array());
	}

	public function action_maintenance()
	{
		$this->template->title = 'Error &raquo; Maintenance';
		$this->template->content = View::forge('error/maintenance', array());
	}

	public function action_congestion()
	{
		$this->template->title = 'Error &raquo; Congestion';
		$this->template->content = View::forge('error/congestion', array());
	}

}
