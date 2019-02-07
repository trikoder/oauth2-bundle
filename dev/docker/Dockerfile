ARG PHP_VERSION=7.3

FROM php:${PHP_VERSION}-cli-alpine3.8
LABEL maintainer="Petar Obradović <petar.obradovic@trikoder.net>"

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
        xdebug-2.7.0RC1 \
        timecop-beta \
    && docker-php-ext-enable \
        xdebug \
        timecop \
    && apk del --purge .build-deps

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
        --version=1.8.3

# Create the user that's going to run our application
RUN useradd -ms /bin/sh app

# Enable parallel package installation for Composer
RUN su-exec app composer global require hirak/prestissimo

COPY entrypoint.sh /usr/local/bin/docker-entrypoint

VOLUME /app/src
WORKDIR /app/src

ENTRYPOINT ["docker-entrypoint"]
CMD ["php", "-a"]
