#!/bin/sh

set -e

usermod -ou ${HOST_USER_ID} app &> /dev/null
groupmod -og ${HOST_GROUP_ID} app &> /dev/null

su-exec app docker-php-entrypoint "$@"
