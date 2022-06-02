FROM php:7.4-apache
RUN docker-php-ext-install mysqli
RUN chmod -R a+r /var/www/html/
