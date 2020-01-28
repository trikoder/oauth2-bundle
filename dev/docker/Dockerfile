ARG PHP_VERSION=7.4

FROM php:${PHP_VERSION}-cli-alpine3.10
LABEL maintainer="Petar Obradović <petar.obradovic@trikoder.net>"

ARG COMPOSER_VERSION=1.9.1
ARG FLEX_VERSION=1.4.8
ARG TIMECOP_VERSION=1.2.10
ARG XDEBUG_VERSION=2.8.0

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
        timecop-${TIMECOP_VERSION} \
    && docker-php-ext-enable \
        xdebug \
        timecop \
    && apk del --purge .build-deps

RUN echo '[xdebug]' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.remote_enable = 1' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.remote_connect_back = 0' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.remote_host = %XDEBUG_REMOTE_HOST%' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini;

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

# Create the user that's going to run our application
RUN useradd -ms /bin/sh app

# Enable parallel package installation for Composer
RUN su-exec app composer global require symfony/flex:${FLEX_VERSION}

COPY entrypoint.sh /usr/local/bin/docker-entrypoint

VOLUME /app/src
WORKDIR /app/src

ENTRYPOINT ["docker-entrypoint"]
CMD ["php", "-a"]
