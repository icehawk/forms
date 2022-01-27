.SILENT:
.PHONY: help

# Based on https://gist.github.com/prwhite/8168133#comment-1313022

## This help screen
help:
	printf "Available commands\n\n"
	awk '/^[a-zA-Z\-\_0-9]+:/ { \
		helpMessage = match(lastLine, /^## (.*)/); \
		if (helpMessage) { \
			helpCommand = substr($$1, 0, index($$1, ":")-1); \
			helpMessage = substr(lastLine, RSTART + 3, RLENGTH); \
			printf "\033[33m%-40s\033[0m %s\n", helpCommand, helpMessage; \
		} \
	} \
	{ lastLine = $$0 }' $(MAKEFILE_LIST)

PROJECT = icehawk-forms
STAGE = development
DOCKER_COMPOSE_OPTIONS = -p $(PROJECT) -f docker-compose.$(STAGE).yml
DOCKER_COMPOSE_UP_SCALE_OPTIONS = --scale nginx=1 --scale web=1
DOCKER_COMPOSE_BASE_COMMAND = CURRENT_UID="$$(id -u):$$(id -g)" docker-compose $(DOCKER_COMPOSE_OPTIONS)
DOCKER_COMPOSE_EXEC_COMMAND = $(DOCKER_COMPOSE_BASE_COMMAND) exec
DOCKER_COMPOSE_RUN_COMMAND = $(DOCKER_COMPOSE_BASE_COMMAND) run --rm
DOCKER_COMPOSE_ISOLATED_RUN_COMMAND = $(DOCKER_COMPOSE_BASE_COMMAND) run --rm --no-deps

CONSOLE_VERBOSITY = -v

## Run all static code analysers and tests
tests: dcvalidate phplint phpstan phpunit
.PHONY: tests

## Run PHP linting
phplint:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) console \
	sh -c "sh /repo/.tools/phplint.sh -p8 -f'*.php' /repo/src /repo/tests"
.PHONY: phplint

## Run PHPStan
phpstan:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) console \
	php /repo/.tools/phpstan.phar analyze --memory-limit=-1
.PHONY: phpstan

## Run Psalm
psalm:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) console \
	php /repo/.tools/psalm.phar
.PHONY: psalm

PHPUNIT_CLI_OPTIONS =

## Run PHPUnit
phpunit:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) console \
	php \
	-derror_reporting=-1 \
	-dmemory_limit=-1 \
	-dxdebug.mode=coverage \
	-dauto_prepend_file=/repo/tests/xdebug-filter.php \
	/repo/.tools/phpunit.phar \
	-c /repo/tests/phpunit.xml \
	$(PHPUNIT_CLI_OPTIONS)
.PHONY: phpunit

## Run docker-compose file validation
dcvalidate:
	$(DOCKER_COMPOSE_BASE_COMMAND) config -q
.PHONY: dcvalidate

## Install whole setup & start docker-compose environment
install: dcbuild dcpull install-tools composer-install
.PHONY: install

## Install whole setup & start docker-compose environment
install-dev: dcbuild dcpull install-tools composer-install-dev
.PHONY: install-dev

## Update whole setup & start docker-compose environment
update: dcbuild dcpull update-tools composer-update
.PHONY: update

## Run composer install for production
composer-install:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) console \
	php /repo/.tools/composer.phar install --no-progress -a -n -v --no-dev --no-suggest
.PHONY: composer-install

## Run composer install for development
composer-install-dev:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) console \
	php /repo/.tools/composer.phar install --no-progress -o -v
.PHONY: composer-install-dev

## Run composer update
composer-update:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) console \
	php /repo/.tools/composer.phar update --no-progress -o -vv
.PHONY: composer-update

## Show outdated dependencies
composer-outdated:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) console \
    php /repo/.tools/composer.phar outdated -D
.PHONY: composer-outdated

phpUnitKey 		= 4AA394086372C20A
phpStanKey 		= CF1A108D0E7AE720
composerKey 	= CBB3D576F2A0946F
trustedKeys 	= "$(phpUnitKey),$(phpStanKey),$(composerKey)"

## Run install & update of tools via Phive
update-tools:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) phive \
	sh -c "php -dmemory_limit=-1 /usr/local/bin/phive --no-progress install --trust-gpg-keys \"$(trustedKeys)\" && php -dmemory_limit=-1 /usr/local/bin/phive --no-progress update"
	curl -L -o "./.tools/phplint.sh" "https://gist.githubusercontent.com/hollodotme/9c1b805e9a2f946433512563edc4b702/raw/60532cb51f1b7a1550216088943bacbd3d4c9351/phplint.sh"
	chmod +x "./.tools/phplint.sh"
.PHONY: update-tools

## Run install of tools via Phive
install-tools:
	$(DOCKER_COMPOSE_ISOLATED_RUN_COMMAND) phive \
	sh -c "php -dmemory_limit=-1 /usr/local/bin/phive --no-progress install --trust-gpg-keys \"$(trustedKeys)\""
	curl -L -o "./.tools/phplint.sh" "https://gist.githubusercontent.com/hollodotme/9c1b805e9a2f946433512563edc4b702/raw/60532cb51f1b7a1550216088943bacbd3d4c9351/phplint.sh"
	chmod +x "./.tools/phplint.sh"
.PHONY: install-tools

## Pull all containers
dcpull:
	$(DOCKER_COMPOSE_BASE_COMMAND) pull
.PHONY: dcpull

## Build all containers
dcbuild:
	docker pull mlocati/php-extension-installer
	COMPOSE_DOCKER_CLI_BUILD=1 \
	DOCKER_BUILDKIT=1 \
	$(DOCKER_COMPOSE_BASE_COMMAND) build --pull --parallel
.PHONY: dcbuild

## Show docker compose container status
dcps:
	$(DOCKER_COMPOSE_BASE_COMMAND) ps
.PHONY: dcps

## Show docker compose setup images
dcimages:
	$(DOCKER_COMPOSE_BASE_COMMAND) images
.PHONY: dcimages

dclogs:
	$(DOCKER_COMPOSE_BASE_COMMAND) logs -f
.PHONY: dclogs