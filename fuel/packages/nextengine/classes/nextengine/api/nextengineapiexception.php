<?php

namespace Nextengine\Api;

/**
 * ネクストエンジンAPIからのレスポンスによって発生する例外クラス
 * 発生する例外やその処理については継承クラスとドキュメントを参照。
 * ドキュメント：http://api.next-e.jp/message.php
 * 
 * @see Nextengine\Api\Client_Router
 * @see Nextengine\Api\Client_Batch
 */
class NextengineApiException extends \FuelException {
	/**
	 * コンストラクタ
	 * 
	 * @param string $message エラーメッセージ(APIからのエラーメッセージをそのまま投げる)
	 * @param string $code    エラーコード(APIからのエラーコードをそのまま投げる)
	 */
	public function __construct($message, $code) {
		// NOTE: 先にcodeプロパティを定義してしまえば、
		//       親クラスのコンストラクタでcodeプロパティが上書きされることはない
		$this->code = $code;
		parent::__construct($message);
	}
}
