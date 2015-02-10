
# sample-fuelphp
PHPでネクストエンジンアプリを作成するためのテンプレートです  
このテンプレートは[FuelPHP](http://fuelphp.jp/docs/1.7/)をベースに作成されています

## ドキュメント
- [ネクストエンジンAPIのドキュメント](http://api.next-e.jp/)
- [このテンプレートのドキュメント](http://api.next-e.jp/sample-fuelphp/)

## Vagrant
[Vagrant](https://www.vagrantup.com/)を使用してローカル環境を構築できます  
boxを追加し、`vagrant up`でVMを起動します  

```
$ vagrant box add --name centos65_64bit https://github.com/2creatives/vagrant-centos/releases/download/v6.5.3/centos65-x86_64-20140116.box
$ cd /path/to/base-fuelphp
$ vagrant up
```

プロビジョニングの設定は全てVagrantfileに書かれています  
そちらをご確認の上、必要であれば修正をお願い致します

### バージョン
VMにインストールされるソフトウェア・パッケージのバージョンは下記の通りです

|項目名	|バージョン	|
|-------|-----------|
|CentOS	|6.5(64bit)	|
|Nginx	|1.0.15		|
|PHP	|5.5		|
|MySQL	|5.6.20		|


### データベースの設定
VM内のデータベースを使用する際は**データベースの設定が必須です**  
`vagrant up`を実行すると、標準出力に下記の内容が出力されます

```
# The random password set for the root user at Mon Aug 11 00:26:15 2014 (local time): sH6kzNxNbpcyCfgV
```

行末の`sH6kzNxNbpcyCfgV`（※この値は実行ごとに変わります）をコピーし、下記手順を行って下さい

```
$ vagrant ssh

[vagrant@vagrant-centos65 ~]$ mysql_secure_installation
Enter current password for root (enter for none): # 今コピーしたパスワードを貼り付け
Change the root password? [Y/n] y
New password:          # 任意のパスワードを入力
Re-enter new password: # 任意のパスワードを入力
Remove anonymous users? [Y/n] y
Disallow root login remotely? [Y/n] y
Remove test database and access to it? [Y/n] y
Reload privilege tables now? [Y/n] y
```

ユーザ名`root`、設定したパスワードでmysqlにログインできれば

> 参考：[DBの接続設定の更新 - ネクストエンジンAPI](http://api.next-e.jp/sample-fuelphp/download.php)

<!---->

> VM内のデータベースはリモートから接続できない設定にしているため、  
> DBに関する操作（DBの作成・マイグレーションなど）はVMにログインした状態で行って下さい
