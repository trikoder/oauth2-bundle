ARG PHP_VERSION=7.2

FROM php:${PHP_VERSION}-cli-alpine3.12
LABEL maintainer="Petar Obradović <petar.obradovic@trikoder.net>"

ARG COMPOSER_VERSION=2.0.2
ARG FLEX_VERSION=1.12.2
ARG PHP_CS_FIXER_VERSION=2.18.4
ARG XDEBUG_VERSION=3.0.3

ENV XDEBUG_START_WITH_REQUEST 0

# This is where we're going to store all of our non-project specific binaries
RUN mkdir -p /app/bin
ENV PATH /app/bin:$PATH

# Install needed core and PECL extensions
RUN apk add --update --no-cache --virtual .build-deps \
        ${PHPIZE_DEPS} \
        libxml2-dev \
        libzip-dev \
        zlib-dev \
    && docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) \
        xml \
        zip \
    && pecl install \
        xdebug-${XDEBUG_VERSION} \
    && docker-php-ext-enable \
        xdebug \
    && apk del --purge .build-deps

RUN mv ${PHP_INI_DIR}/php.ini-development ${PHP_INI_DIR}/php.ini

RUN echo '[xdebug]' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.mode = debug' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.start_with_request = ${XDEBUG_START_WITH_REQUEST}' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.discover_client_host = 0' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.client_host = %XDEBUG_CLIENT_HOST%' >> ${PHP_INI_DIR}/conf.d/docker-php-ext-xdebug.ini

# Utilities needed to run this image
RUN apk add --update --no-cache \
        git \
        libzip \
        unzip \
        su-exec \
        shadow

# Composer
RUN curl --show-error https://getcomposer.org/installer | php -- \
        --install-dir=/app/bin \
        --filename=composer \
        --version=${COMPOSER_VERSION}

# PHP-CS-Fixer
RUN curl --location --output /app/bin/php-cs-fixer --show-error \
        https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v${PHP_CS_FIXER_VERSION}/php-cs-fixer.phar \
    && chmod a+x /app/bin/php-cs-fixer

# Create the user that's going to run our application
RUN useradd -ms /bin/sh app

# Enable parallel package installation for Composer
RUN su-exec app composer global require symfony/flex:${FLEX_VERSION}

COPY entrypoint.sh /usr/local/bin/docker-entrypoint

VOLUME /app/src
WORKDIR /app/src

ENTRYPOINT ["docker-entrypoint"]
CMD ["php", "-a"]
