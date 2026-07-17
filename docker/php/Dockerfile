# =============================================================================
# AGC Assessors - Dockerfile (Desarrollo local)
# PHP 8.4 + Nginx con Xdebug para debugging
# No compila assets aquí — eso lo hace el contenedor node
# =============================================================================

FROM serversideup/php:8.4-fpm-nginx

USER root

RUN apt-get update \
    && apt-get install -y --no-install-recommends git \
    && rm -rf /var/lib/apt/lists/*

RUN install-php-extensions \
    intl \
    gd \
    bcmath \
    exif \
    xsl \
    sockets

# ServerSideUp must start as root so its entrypoint can configure Nginx.
# VS Code still opens the workspace as www-data via devcontainer.json.
USER root
