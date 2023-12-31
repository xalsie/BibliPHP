FROM php:8.2-apache

RUN apt-get update && apt-get -y upgrade

# Install Postgre PDO
RUN apt-get update -y && apt-get install -y libpq-dev libpng-dev libfreetype6-dev

RUN usermod -u 1000 www-data

RUN a2enmod rewrite
