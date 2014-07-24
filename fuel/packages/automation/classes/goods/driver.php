<?php

namespace Automation\Goods;

abstract class Driver extends \Automation\Driver
{
	/**
	 * NE APIから取得した代表商品コード付きの商品のデータをモール・カートへする
	 * @param array         $ne_goods NE APIから取得した商品データ
	 * @param \Model_Company $company  その商品が登録されている企業
	 * @param \Model_User    $user     $companyに属しており、NE APIを使用しているユーザ
	 * @return array
	 * @todo 戻り値の仕様を決める
	 */
	abstract public function save(array $ne_goods, \Model_Company $company, \Model_User $user);

	/**
	 * NE APIから取得した商品のデータを利用してモール・カートから商品を削除する
	 * @param array         $ne_goods NE APIから取得した商品データ
	 * @param \Model_Company $company  その商品が登録されている企業
	 * @param \Model_User    $user     $companyに属しており、NE APIを使用しているユーザ
	 * @return array
	 * @todo 戻り値の仕様を決める
	 */
	abstract public function save_variation(array $ne_goods, \Model_Company $company, \Model_User $user);

	/**
	 * NE APIから取得した（既にモール・カートへ登録済みの）商品のデータの変更をモール・カートへ反映する
	 * @param array         $ne_goods NE APIから取得した商品データ
	 * @param \Model_Company $company  その商品が登録されている企業
	 * @param \Model_User    $user     $companyに属しており、NE APIを使用しているユーザ
	 * @return array
	 * @todo 戻り値の仕様を決める
	 */
	abstract public function delete(array $ne_goods, \Model_Company $company, \Model_User $user);

	/**
	 * NE APIから取得した代表商品コード付き商品のデータを利用してモール・カートから商品を削除する
	 * @param array         $ne_goods NE APIから取得した商品データ
	 * @param \Model_Company $company  その商品が登録されている企業
	 * @param \Model_User    $user     $companyに属しており、NE APIを使用しているユーザ
	 * @return array
	 * @todo 戻り値の仕様を決める
	 */
	abstract public function delete_variation(array $ne_goods, \Model_Company $company, \Model_User $user);
}
