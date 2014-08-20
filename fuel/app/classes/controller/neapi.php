<?php

/**
 * ネクストエンジンAPIを利用する際に継承する抽象クラス
 * APIを使用する画面で共通処理として必要なセッション処理、クライアントの初期化を行う。
 * 
 * NOTE: 継承クラスで別途_initの処理を書きたくなった際には、「必ず」parent::_initをコールして下さい。
 *       こいつを呼んでもらえないと言語ファイルのロードが出来ません。
 */
abstract class Controller_Neapi extends \Base\Controller_Neapi {}
