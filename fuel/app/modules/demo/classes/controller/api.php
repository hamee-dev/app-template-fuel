<?php

namespace Demo;

class Controller_Api extends \Controller_Template {
	public function action_find()
	{
		$this->template->title = 'Demo » Api » find';
		$this->template->content = \View::forge('api/find');
	}
}
