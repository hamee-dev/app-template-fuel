
# base-fuelphp
社内用アプリの汎用テンプレートです。

## Vagrantfile
- Nginx
- ssl
- MySQL 5.6
- PHP5.4
- XDebug（不要なら消して下さい。）

をVagrantfileに詰め込んであります。

### バージョン
具体的なバージョンは以下の通りです。

|項目名	|バージョン	|
|-------|-----------|
|CentOS	|6.5(64bit)	|
|Nginx	|1.0.15		|
|PHP	|5.4.31		|
|MySQL	|5.6.20		|


### 立ち上げ
boxを追加し、`vagrant up`でVMを起動します。

```
$ vagrant box add --name centos65_64bit https://github.com/2creatives/vagrant-centos/releases/download/v6.5.3/centos65-x86_64-20140116.box
$ cd /path/to/base-fuelphp
$ vagrant up
```

### データベースの設定
**データベースの設定が必須です。**  
`vagrant up`すると、末尾に下記のような出力があると思います。

`# The random password set for the root user at Mon Aug 11 00:26:15 2014 (local time): sH6kzNxNbpcyCfgV`

この行末の`sH6kzNxNbpcyCfgV`（※実行ごとに変わる）をコピーして、下記手順を踏んで下さい。

```
$ vagrant ssh

[vagrant@vagrant-centos65 ~]$ mysql_secure_installation
Enter current password for root (enter for none): # 今コピーしたパスワードを貼り付け
Change the root password? [Y/n] y
New password: # 任意のパスワードを入力
Re-enter new password: # 任意のパスワードを入力
Remove anonymous users? [Y/n] y
Disallow root login remotely? [Y/n] y
Remove test database and access to it? [Y/n] y
Reload privilege tables now? [Y/n] y
```

接続情報を設定したら、使用するデータベースを作成して下さい。

**なお、リモートからの接続ができないようになっているため、**  
**DBに関する操作（DBの作成・マイグレーションなど）は`vagrant ssh`でVMにログインした状態で行って下さい。**

### DBの接続情報を設定

設定したパスワードを元に、DBの接続情報を変更します。  
下記に設定例を記します。

```php
<?php

return array(
	'default' => array(
		'connection'  => array(
			'dsn'        => 'mysql:host=127.0.0.1;dbname=app_base_dev',
			'username'   => 'root',
			'password'   => 'hamee831',
		),
	),
);
```

|項目名		|値					|
|-----------|-------------------|
|host		|127.0.0.1			|
|dbname		|作成したデータベース名	|
|username	|root				|
|password	|設定したパスワード		|

Sequel Pro等のGUIクライアントを使用する場合は、

|項目名		|値					|
|-----------|-------------------|
|接続		|SSH				|
|SSHホスト	|192.168.33.10		|
|SSHユーザ	|vagrant			|
|SSH鍵		|~/.vagrant.d/insecure_private_key を指定、パスフレーズなし|

と設定して下さい。

### 動作確認
ブラウザから`https://192.168.33.10`にアクセスするとWelcomeページが見られます。

`https://192.168.33.10/auth/login`にアクセスして、  
「Authenticate complete!!」と出力されれば、DBの接続確認および、FuelPHPの動作確認は完了です。

## マイグレーション {#migrations}

### アプリ独自のマイグレーションの作成
基盤として提供しているマイグレーションは、  
`base`パッケージの中の`migrations`ディレクトリに入っています。  

基盤として提供されているマイグレーションからカラムを追加したり削除を行う場合には、  
`fuel/packages/base/migrations`ではなく、`fuel/app/migrations`にマイグレーションファイルを作成して下さい。

> ※ `$ php oil g migration ***...`と記述すれば自動で`fuel/app/migrations`に作成されるため、特に問題はないかと思います。

### マイグレーションを実行
マイグレーションは`base`パッケージの中に入っているため、  
通常のマイグレーションではなく`--packages`オプションをつけて実行します。

また、アプリ独自のマイグレーションを作成した場合には、  
**baseパッケージのマイグレーションを実行した後に、`--packages`オプション無しで** マイグレーションを実行します

```shell
$ vagrant ssh

[vagrant@vagrant-centos65 ~]$ cd /vagrant/
[vagrant@vagrant-centos65 vagrant]$ php oil r migrate --packages=base
[vagrant@vagrant-centos65 vagrant]$ php oil r migrate
```

