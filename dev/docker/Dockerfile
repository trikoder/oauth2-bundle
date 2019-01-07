FROM php:7.2.13-cli-alpine3.8

# This is where we're going to store all of our non-project specific binaries
RUN mkdir -p /app/bin
ENV PATH /app/bin:$PATH

# Install needed core and PECL extensions
RUN apk add --update --no-cache --virtual .build-deps \
        ${PHPIZE_DEPS} \
        libxml2-dev \
        zlib-dev \
    && docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) \
        xml \
        zip \
    && pecl install \
        xdebug-2.6.1 \
    && docker-php-ext-enable \
        xdebug \
    && apk del --purge .build-deps

# Utilities needed to run this image
RUN apk add --update --no-cache \
        git \
        unzip \
        su-exec \
        shadow

# Composer
RUN curl --show-error https://getcomposer.org/installer | php -- \
        --install-dir=/app/bin \
        --filename=composer \
        --version=1.8.0

# Create the user that's going to run our application
RUN useradd -ms /bin/sh app

COPY entrypoint.sh /usr/local/bin/docker-entrypoint

VOLUME /app/src
WORKDIR /app/src

ENTRYPOINT ["docker-entrypoint"]
CMD ["php", "-a"]
