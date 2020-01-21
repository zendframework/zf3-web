FROM php:7.1-fpm

# System dependencies
RUN apt-get update
RUN apt-get install -y git zlib1g-dev wget

# PHP Extensions
RUN docker-php-ext-install zip

# NodeJS
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash - && \
  apt-get install -y nodejs

# Gulp
RUN npm install -g gulp

WORKDIR /var/vhosts/framework.zend.com
