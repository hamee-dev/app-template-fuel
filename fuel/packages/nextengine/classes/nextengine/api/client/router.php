<?php

namespace Nextengine\Api;

class Client_Router extends Client
{
	/**
	 * 例外の振り分けを行う
	 */
	protected function failover($code, $message) {
		// 親クラスが例外を投げてくれるのでその詳細を解析する
		try {
			parent::failover($code, $message);
		} catch(NextengineApiException $e) {
			// NOTE: redirectはexitを打つのでbreakを書かないでも動作するが、
			//       redirectじゃない処理に変えた時に意図しないバグを防ぐためbreakを入れている。
			switch($e->getCode()) {
				// メンテナンス中 => メンテナンス中です画面へリダイレクト
				case '002007':	// 現在ネクストエンジンサーバーがメンテナンス中の為、再度時間を置いてからアクセスして下さい。
				case '003004':	// 現在メイン機能サーバーがメンテナンス中の為、再度時間を置いてからアクセスして下さい。
					\Response::redirect('/error/maintenance');
					break;

				// 混み合ってます、受注取り込み中です => しばらく待ってアクセスして下さい画面へリダイレクト
				case '003001':	// 現在メイン機能サーバーが混み合っておりますので、再度時間を置いてからアクセスして下さい。
				case '003002':	// 現在メイン機能サーバーが混み合っておりますので、再度時間を置いてからアクセスして下さい。
				case '008003':	// 受注取込中のため、更新出来ません。
				case '008007':	// 納品書印刷中の伝票があります。時間を空けて再度APIを実行して下さい。
				case '008010':	// 棚卸中のため、更新出来ません。
					\Response::redirect('/error/congestion');
					break;

				// 支払い灯の理由で利用停止、システムエラー => 営業に問い合わせてねエラー画面へリダイレクト
				case '001007':	// [xxxxx]様のネクストエンジンが、次の理由により利用停止になっています。[xxxxx]
				case '002003':	// [xxxxx]様のネクストエンジンが、次の理由により利用停止になっています。[xxxxx]
				case '003003':	// [xxxxx]様のメイン機能が、利用停止です。
				case '999999':	// APIサーバーのシステムエラーが発生しました。
					\Response::redirect('/error');
					break;

				default:
					throw $e;
			}
		}
	}
}
