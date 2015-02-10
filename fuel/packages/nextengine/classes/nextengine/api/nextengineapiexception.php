<?php

namespace Nextengine\Api;

/**
 * ネクストエンジンAPIからのレスポンスによって発生する例外クラス
 * 
 * 発生する例外やその処理については継承クラスとドキュメントを参照。
 */
class NextengineApiException extends \FuelException {
	/**
	 * コンストラクタ
	 * 
	 * @param string $message エラーメッセージ(APIからのエラーメッセージをそのまま投げる)
	 * @param string $code    エラーコード(APIからのエラーコードをそのまま投げる)
	 * @see http://api.next-e.jp/message.php ネクストエンジンAPI
	 */
	public function __construct($message, $code) {
		// NOTE: 先にcodeプロパティを定義してしまえば、
		//       親クラスのコンストラクタでcodeプロパティが上書きされることはない
		$this->code = $code;
		parent::__construct($message);
	}
}
