<?php

namespace Demo;

class Controller_Api extends \Controller_Neapi {
	public function action_find()
	{
		$data = array(
			'products' => self::$client->apiExecute('/api_v1_master_goods/search', array(
				'fields' => 'goods_id,goods_name,stock_quantity,supplier_name',
				'limit'  => 5
			)),
			'divisions' => array(
				'order'  => self::$client->apiExecute('/api_v1_system_order/info'),
				'credit' => self::$client->apiExecute('/api_v1_system_credittype/info'),
			)
		);

		$this->template->title = 'Demo » Api » find';
		$this->template->content = \View::forge('api/find', $data);
	}
}
