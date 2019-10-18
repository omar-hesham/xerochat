FROM nrstech/php-7.2-apache

RUN if [ $IS_DEV = "true" ]; \
    then rm -f /usr/local/etc/php/conf.d/opcache.ini; \
    fi;
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli;

WORKDIR /var/www