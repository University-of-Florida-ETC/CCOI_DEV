FROM php:7.4-apache
RUN a2enmod rewrite
RUN a2ensite default-ssl
#RUN docker-php-ext-install mysqli
RUN chmod -R a+r /var/www/html/
