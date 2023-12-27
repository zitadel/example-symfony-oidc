FROM mcr.microsoft.com/devcontainers/php:1-8.2-bullseye
RUN apt-get -y update && \
    apt-get install -y libicu-dev && \
    docker-php-ext-install intl
RUN mv "${PHP_INI_DIR}/php.ini-development" "${PHP_INI_DIR}/php.ini"
