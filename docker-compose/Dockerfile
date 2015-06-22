FROM php:5.6-apache
# Install modules
RUN apt-get update && apt-get install -y libxml2-dev \
    && docker-php-ext-install soap \
    && a2enmod rewrite
