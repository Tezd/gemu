FROM php:5.6-apache

RUN apt-get update && \
    apt-get install -y libxml2-dev && \
    docker-php-ext-install soap

RUN a2enmod rewrite

COPY . /var/www/html/

RUN cd /var/www/html && \
    php ./bin/composer install
