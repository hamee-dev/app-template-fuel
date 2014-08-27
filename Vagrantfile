# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

# Please install `centos65_64bit` box
# $ vagrant box add --name centos65_64bit https://github.com/2creatives/vagrant-centos/releases/download/v6.5.3/centos65-x86_64-20140116.box

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  # Every Vagrant virtual environment requires a box to build off of.
  config.vm.box = "centos65_64bit"

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  config.vm.network "private_network", ip: "192.168.33.10"

  config.vm.synced_folder ".", "/vagrant", mount_options: ['dmode=777','fmode=777']

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  config.vm.provider "virtualbox" do |vb|
  #   # Don't boot with headless mode
  #   vb.gui = true
  #
  #   # Use VBoxManage to customize the VM. For example to change memory:
    vb.customize ["modifyvm", :id, "--memory", "1024"]
  end
  #
  # View the documentation for the provider you're using for more
  # information on available options.

  ##################################
  # Nginx server config
  ##################################
  ssl_conf = <<-'CONF'
# HTTPS server configuration
server {
  listen       443 ssl;

  ssl                  on;
  ssl_certificate      /home/vagrant/keys/localhost.crt;
  ssl_certificate_key  /home/vagrant/keys/localhost.key;

  access_log /var/log/nginx/localhost.access.log combined;
  error_log /var/log/nginx/localhost.error.log warn;

  charset UTF-8;
  root   /vagrant/public;

  location / {
    autoindex on;
    index     index.php index.html;
    try_files \$uri \$uri/ /index.php?\$query_string;
  }

  location ~ \.php$ {
    fastcgi_pass   127.0.0.1:9000;
    fastcgi_index  index.php;
    fastcgi_param  SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;
    include        fastcgi_params;
  }
}
  CONF

  ##################################
  # Inline shell setup
  ##################################
  config.vm.provision "shell", :inline => <<-SHELL
    # --- upgrade
    yum update -y
    yum upgrade -y

    # --- generate ssl keys
    mkdir keys && cd keys
    openssl genrsa 2048 > localhost.key
    openssl req -new -key localhost.key > localhost.csr << EOF
JP
Tokyo
Odawara-shi
Hamee

localhost
hoge@example.com


EOF
    openssl x509 -days 3650 -req -signkey localhost.key < localhost.csr > localhost.crt
    cd ../
    chown -R vagrant:vagrant keys

    # --- MySQL 5.6.*(http://ips.nekotype.com/3420/)
    mkdir src && cd src
    yum install -y wget
    wget http://dev.mysql.com/get/Downloads/MySQL-5.6/MySQL-5.6.20-1.linux_glibc2.5.x86_64.rpm-bundle.tar
    tar -xvf MySQL-5.6.20-1.linux_glibc2.5.x86_64.rpm-bundle.tar
    rm -rf MySQL-5.6.20-1.linux_glibc2.5.x86_64.rpm-bundle.tar

    yum localinstall -y \
      MySQL-shared-compat-5.6.20-1.linux_glibc2.5.x86_64.rpm\
      MySQL-server-5.6.20-1.linux_glibc2.5.x86_64.rpm\
      MySQL-client-5.6.20-1.linux_glibc2.5.x86_64.rpm\
      MySQL-devel-5.6.20-1.linux_glibc2.5.x86_64.rpm
    cd ../
    rm -rf src

    # --- Nginx
    rpm -Uvh http://download.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
    rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
    yum install -y nginx --enablerepo=remi
    # --- Nginx -> set document root
    rm -rf /usr/share/nginx/html
    ln -fs /vagrant/public /usr/share/nginx/html
    # --- Nginx -> set ssl server config
    sudo sh -c 'echo "#{ssl_conf}" > /etc/nginx/conf.d/ssl.conf'
    # --- Nginx -> create log files
    touch /var/log/nginx/localhost.access.log
    touch /var/log/nginx/localhost.error.log

    # --- PHP-fpm
    yum install -y php-fpm php-common --enablerepo=remi

    # --- PHP
    yum install -y php php-pecl-apc php-cli php-pear php-pdo php-mysqlnd.x86_64 php-pgsql php-pecl-mongo php-sqlite php-pecl-memcache php-pecl-memcached php-gd php-mbstring php-mcrypt php-xml --enablerepo=remi
    # --- PHP -> install XDebug
    yum install -y php-pecl-xdebug.x86_64 --enablerepo=remi
    # --- PHP -> set default timezone(Asia/Tokyo)
    sed -i -e 's|;date.timezone =|date.timezone = '\''Asia/Tokyo'\''|' /etc/php.ini

    # --- Date
    cp /usr/share/zoneinfo/Japan /etc/localtime
    sh -c "echo 'ZONE=\"Asia/Tokyo\"' > /etc/sysconfig/clock"
    yum install -y ntpdate.x86_64
    ntpdate ntp.nict.jp
    # --- Date -> MySQL date config
    sudo sed "s/\[mysqld\]/[mysqld]\ndefault-time-zone='+9:00'/" /etc/my.cnf -i

    # --- setup
    service nginx start
    service php-fpm start

    chkconfig httpd off

    chkconfig --add nginx
    chkconfig --levels 235 nginx on
    chkconfig --add php-fpm
    chkconfig --levels 235 php-fpm on

    chkconfig --add php-fpm

    sudo service mysql start
    echo
    echo "=== INFO: please login('vagrant ssh') and run 'mysql_secure_installation' initial password:"
    cat /root/.mysql_secret
  SHELL
end
