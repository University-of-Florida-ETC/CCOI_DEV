FROM php:7.4-apache
RUN docker-php-ext-install mysqli
RUN a2enmod ssl && a2enmod socache_shmcb
RUN a2enmod rewrite
RUN a2ensite default-ssl
#RUN docker-php-ext-install mysqli
RUN chmod -R a+r /var/www/html/
