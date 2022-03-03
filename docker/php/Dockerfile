FROM php:8.0-fpm

RUN apt-get update \
	&& apt-get install -y unzip wget

RUN docker-php-ext-enable opcache

RUN wget https://composer.github.io/installer.sig -qO composer.sig \
	&& echo "  composer-setup.php" >> composer.sig \
	&& wget https://getcomposer.org/installer -qO composer-setup.php \
	&& sha384sum -c composer.sig \
	&& php composer-setup.php --quiet --install-dir /usr/local/bin --filename composer \
	&& rm -rf composer.sig composer-setup.php

WORKDIR /var/www/html
