#!/bin/sh

set -e

usermod -ou "${HOST_USER_ID}" app &> /dev/null
groupmod -og "${HOST_GROUP_ID}" app &> /dev/null

HOST_IP="${HOST_IP:-$(ip route | grep ^default | awk '{ print $3 }')}"

sed -i "s/%XDEBUG_REMOTE_HOST%/$HOST_IP/" \
    /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

su-exec app docker-php-entrypoint "$@"
