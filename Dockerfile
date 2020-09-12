## Install Apache HTTP Server and PHP with MySQLi extension
FROM php:7.2-apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

## Install OpenSSH server, Certbot and OpenSSL
RUN apt-get update
RUN apt-get -y upgrade
RUN apt-get install -y openssh-server certbot openssl

## Copy all website files
ARG mySqlHost=[TBD]
ARG mySqlDatabase=[TBD]
ARG realm=[TBD]
COPY src/mrgottham/ /var/www/mrgottham
COPY src/patrick/ /var/www/patrick
COPY src/mathias/ /var/www/mathias
COPY src/sbk/ /var/www/sbk
RUN mkdir -p /var/www/mrgottham/.well-known/acme-challenge
RUN sed -i "s/MySqlHost = \[TBD\]/MySqlHost = ${mySqlHost}/g" /var/www/mrgottham/config.ini
RUN sed -i "s/MySqlDatabase = \[TBD\]/MySqlDatabase = ${mySqlDatabase}/g" /var/www/mrgottham/config.ini
RUN sed -i "s/Realm = \[TBD\]/Realm = ${realm}/g" /var/www/mrgottham/config.ini
RUN sed -i "s/MySqlHost = \[TBD\]/MySqlHost = ${mySqlHost}/g" /var/www/patrick/config.ini
RUN sed -i "s/MySqlDatabase = \[TBD\]/MySqlDatabase = ${mySqlDatabase}/g" /var/www/patrick/config.ini
RUN sed -i "s/Realm = \[TBD\]/Realm = ${realm}/g" /var/www/patrick/config.ini
RUN sed -i "s/MySqlHost = \[TBD\]/MySqlHost = ${mySqlHost}/g" /var/www/mathias/config.ini
RUN sed -i "s/MySqlDatabase = \[TBD\\]/MySqlDatabase = ${mySqlDatabase}/g" /var/www/mathias/config.ini
RUN sed -i "s/Realm = \[TBD\]/Realm = ${realm}/g" /var/www/mathias/config.ini
RUN sed -i "s/MySqlHost = \[TBD\]/MySqlHost = ${mySqlHost}/g" /var/www/sbk/config.ini
RUN sed -i "s/MySqlDatabase = \[TBD\]/MySqlDatabase = ${mySqlDatabase}/g" /var/www/sbk/config.ini
RUN sed -i "s/Realm = \[TBD\]/Realm = ${realm}/g" /var/www/sbk/config.ini

## Setup Apache HTTP Server
ARG reduceFoodWasteHost=[TBD]
RUN a2enmod rewrite
RUN a2enmod proxy
RUN a2enmod proxy_http
RUN a2enmod headers
RUN a2enmod ssl
RUN service apache2 restart
COPY config/apache2/ssl/star.osdevgrp.local.crt /etc/apache2/ssl/star.osdevgrp.local.crt
COPY config/apache2/ssl/star.osdevgrp.local.key /etc/apache2/ssl/star.osdevgrp.local.key
COPY config/apache2/sites-available/websites.conf /etc/apache2/sites-available/websites.conf
COPY config/apache2/sites-available/websites-ssl.conf /etc/apache2/sites-available/websites-ssl.conf
COPY config/apache2/sites-available/mrgottham.conf /etc/apache2/sites-available/mrgottham.conf
COPY config/apache2/sites-available/osdevgrp.conf /etc/apache2/sites-available/osdevgrp.conf
COPY config/apache2/sites-available/formindskmadspild.conf /etc/apache2/sites-available/formindskmadspild.conf
RUN sed -i "s/\[TBD\]/${reduceFoodWasteHost}/g" /etc/apache2/sites-available/formindskmadspild.conf
RUN rm -rf /etc/apache2/sites-enabled/*
RUN ln -s /etc/apache2/sites-available/websites.conf /etc/apache2/sites-enabled/websites.conf
RUN ln -s /etc/apache2/sites-available/websites-ssl.conf /etc/apache2/sites-enabled/websites-ssl.conf
RUN ln -s /etc/apache2/sites-available/mrgottham.conf /etc/apache2/sites-enabled/mrgottham.conf
RUN ln -s /etc/apache2/sites-available/osdevgrp.conf /etc/apache2/sites-enabled/osdevgrp.conf
RUN ln -s /etc/apache2/sites-available/formindskmadspild.conf /etc/apache2/sites-enabled/formindskmadspild.conf

## Setup PHP
ARG mySqlDefaultUser=[TBD]
COPY config/php/php.ini /usr/local/etc/php/
RUN sed -i "s/mysqli.default_user = \[TBD\]/mysqli.default_user = ${mySqlDefaultUser}/g" /usr/local/etc/php/php.ini

## Setup OpenSSH server
ARG sshPassword=[TBD]
RUN mkdir /var/run/sshd
RUN echo "root:${sshPassword}" | chpasswd
RUN sed -i "s/#PermitRootLogin prohibit-password/PermitRootLogin yes/g" /etc/ssh/sshd_config
RUN sed -i "s/#Port 22/Port 2222/g" /etc/ssh/sshd_config
RUN sed -i "/#!\/bin\/sh/a\/etc\/init.d\/ssh start" /usr/local/bin/docker-php-entrypoint

## Setup Certbot
VOLUME ["/etc/letsencrypt", "/var/lib/letsencrypt"]

## Expose ports
EXPOSE 80 443 2222