FROM serversideup/php:8.4-fpm-nginx

USER root

RUN install-php-extensions \
    intl \
    gd \
    bcmath \
    exif \
    xsl \
    sockets

USER www-data
