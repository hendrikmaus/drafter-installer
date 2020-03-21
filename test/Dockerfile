FROM composer:latest AS composer

FROM php:7
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN apt-get update -qq && apt-get install -y git python build-essential
ENV COMPOSER_HOME /tmp
RUN chmod -R 0777 /tmp
