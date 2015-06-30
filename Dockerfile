FROM php:5.6-apache

RUN apt-get update && \
    apt-get install -y libxml2-dev zlib1g-dev git && \
    docker-php-ext-install soap && \
    docker-php-ext-install zip

RUN a2enmod rewrite

ADD docker-compose/etc/php/conf.d/timezone.ini /usr/local/etc/php/conf.d/timezone.ini

COPY . /var/www/html/

RUN cd /var/www/html && \
    php ./bin/composer install
