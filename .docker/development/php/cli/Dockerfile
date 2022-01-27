FROM php:8.0-cli-alpine AS builder

RUN set -ex && apk update && apk upgrade --available

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN set -ex && install-php-extensions json-stable xdebug-stable

FROM builder AS console