<?php

namespace Automation\Recieved;

abstract class Driver extends \Automation\Driver
{
	/**
	 * モール・カートの受注一覧を取得する
	 * @return array
	 * @todo 戻り値の仕様を決める
	 */
	abstract public function fetch_mall_orders();

	/**
	 * NE APIから取得したメイン機能の受注情報をモール・カートへ適用する  
	 * 注文の更新がされた場合などにカート・モール側の受注情報の変更する場合などを想定している。
	 * @param array         $ne_goods NE APIから取得した受注データ
	 * @param \Model_Company $company  その受注が登録されている企業
	 * @param \Model_User    $user     $companyに属しており、NE APIを使用しているユーザ
	 * @return array
	 * @todo 戻り値の仕様を決める
	 */
	abstract public function apply_main_function_to_mall(array $order, \Model_Company $company, \Model_User $user);

	/**
	 * 【フック】メイン機能側で受注伝票が出荷確定された後に実行されるフック。  
	 * デフォルトではなにもしないので、必要であれば拡張すること。  
	 * 例えばモール・カート側の受注状況を変更したり、購入者へメールを送信するなどが考えられる。
	 * @param array $order NE APIから取得した受注伝票のデータ
	 * @return void
	 */
	public function after_shiped(array $order) {}
}
