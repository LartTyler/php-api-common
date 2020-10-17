# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  config.vm.box = "bento/ubuntu-19.10"

  config.vm.provision "bootstrap", type: "shell", inline: <<-SHELL
    if grep -Fqvx "^/swapfile" /etc/fstab; then
      fallocate -l 4G /swapfile
      chmod 600 /swapfile

      mkswap /swapfile
      swapon /swapfile

      echo -e '/swapfile\tnone\tswap\tsw\t0\t0' >> /etc/fstab
    fi

    apt-get update -y
    add-apt-repository -y ppa:ondrej/php

    apt-get install -y ntp php7.3-common php7.3-cli php7.3-curl php7.3-zip php7.3-zip php7.3-mbstring php7.3-xml \
      php7.3-xdebug php7.3-json
    apt-get install -y composer

    if grep -Fqvx "xdebug.remote_enable" /etc/php/7.3/mods-available/xdebug.ini; then
      echo "xdebug.remote_enable = on" >> /etc/php/7.3/mods-available/xdebug.ini
      echo "xdebug.remote_connect_back = on" >> /etc/php/7.3/mods-available/xdebug.ini
      echo "xdebug.idekey = application" >> /etc/php/7.3/mods-available/xdebug.ini
      echo "xdebug.remote_autostart = on" >> /etc/php/7.3/mods-available/xdebug.ini
      echo "xdebug.remote_host = 10.0.2.2" >> /etc/php/7.3/mods-available/xdebug.ini
    fi
  SHELL

  config.vm.provision "install", type: "shell", privileged: false, inline: <<-SHELL
    echo "----------------------------------------------"
    echo "Initializing project..."

    cd /vagrant
    composer install
  SHELL

  config.vm.provision "root-run", type: "shell", run: "always", inline: <<-SHELL
    systemctl stop ntp
    ntpd -gq > /dev/null
    systemctl start ntp
  SHELL
end
