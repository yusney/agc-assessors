# =============================================================================
# AGC Assessors - Dockerfile (Desarrollo local)
# PHP 8.4 + Nginx con Xdebug para debugging
# No compila assets aquí — eso lo hace el contenedor node
# =============================================================================

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
