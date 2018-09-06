## Install Apache HTTP Server and PHP with MySQLi extension
FROM php:7.2-apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

## Copy all website files
COPY src/mrgottham/ /var/www/mrgottham
COPY src/patrick/ /var/www/patrick
COPY src/mathias/ /var/www/mathias
COPY src/sbk/ /var/www/sbk

## Setup Apache HTTP Server
RUN a2enmod rewrite
RUN a2enmod proxy
RUN a2enmod proxy_http
RUN a2enmod ssl
RUN service apache2 restart
COPY config/apache2/ssl/star.osdevgrp.local.crt /etc/apache2/ssl/star.osdevgrp.local.crt
COPY config/apache2/ssl/star.osdevgrp.local.key /etc/apache2/ssl/star.osdevgrp.local.key
COPY config/apache2/sites-available/websites.conf /etc/apache2/sites-available/websites.conf
COPY config/apache2/sites-available/websites-ssl.conf /etc/apache2/sites-available/websites-ssl.conf
COPY config/apache2/sites-available/mrgottham.conf /etc/apache2/sites-available/mrgottham.conf
COPY config/apache2/sites-available/osdevgrp.conf /etc/apache2/sites-available/osdevgrp.conf
COPY config/apache2/sites-available/formindskmadspild.conf /etc/apache2/sites-available/formindskmadspild.conf
RUN rm -rf /etc/apache2/sites-enabled/*
RUN ln -s /etc/apache2/sites-available/websites.conf /etc/apache2/sites-enabled/websites.conf
RUN ln -s /etc/apache2/sites-available/websites-ssl.conf /etc/apache2/sites-enabled/websites-ssl.conf
RUN ln -s /etc/apache2/sites-available/mrgottham.conf /etc/apache2/sites-enabled/mrgottham.conf
RUN ln -s /etc/apache2/sites-available/osdevgrp.conf /etc/apache2/sites-enabled/osdevgrp.conf
RUN ln -s /etc/apache2/sites-available/formindskmadspild.conf /etc/apache2/sites-enabled/formindskmadspild.conf

## Setup PHP
COPY config/php/php.ini /usr/local/etc/php/

EXPOSE 80 443