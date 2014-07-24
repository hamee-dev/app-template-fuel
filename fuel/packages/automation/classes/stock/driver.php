<?php

namespace Automation\Stock;

abstract class Driver extends \Automation\Driver
{
	/**
	 * NE APIから取得した商品・在庫情報を、モール・カートの在庫情報へ適用する
	 * @param array         $ne_goods NE APIから取得した商品・在庫データ
	 * @param \Model_Company $company  その商品・在庫が登録されている企業
	 * @param \Model_User    $user     $companyに属しており、NE APIを使用しているユーザ
	 * @return mixed
	 * @todo 戻り値の仕様を決める
	 */
	abstract public function apply_main_function_to_mall(array $ne_goods, \Model_Company $company, \Model_User $user);

	/**
	 * メイン機能の商品コードを利用して、モール・カート側の在庫数を取得して返す
	 * @param string $ne_goods_id                NE APIから取得した商品コード
	 * @param string $ne_goods_representation_id NE APIから取得した代表商品コード（代表商品コードがない場合はnull）
	 * @return array
	 * @todo 戻り値の仕様を決める
	 */
	abstract public function cart_stocks($ne_goods_id, $ne_goods_representation_id = null);
}
