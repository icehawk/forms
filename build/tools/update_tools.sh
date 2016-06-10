#!/usr/bin/env bash

cd "$(dirname "$0")"

rm -rf ./*.phar

# Composer
curl -sS 'https://getcomposer.org/installer' | php --

# PHPUNIT
wget -c https://phar.phpunit.de/phpunit.phar

# PHPLOC
wget -c https://phar.phpunit.de/phploc.phar

# PHP_DEPEND
wget -c http://static.pdepend.org/php/latest/pdepend.phar

# PHP Mess Detector
wget -c http://static.phpmd.org/php/latest/phpmd.phar

# PHP Code Sniffer
wget -c https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar

# PHP Copy Paste Detector
wget -c https://phar.phpunit.de/phpcpd.phar

# PHP Dox
PHPDOX_VERSION='0.8.0'
wget "https://github.com/theseer/phpdox/releases/download/0.8.0/phpdox-$PHPDOX_VERSION.phar"
mv "phpdox-$PHPDOX_VERSION.phar" phpdox.phar

chmod +x ./*.phar
