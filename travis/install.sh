#!/usr/bin/env bash

set -e

COMPOSER_PREFER_LOWEST=${COMPOSER_PREFER_LOWEST-false}
DOCKER_BUILD=${DOCKER_BUILD-false}
SYMFONY_VERSION=${SYMFONY_VERSION-2.7.*}
TRAVIS_PHP_VERSION=${TRAVIS_PHP_VERSION-5.6}

if [ "$DOCKER_BUILD" = true ]; then
    cp .env.dist .env

    docker-compose build
    docker-compose run --rm php composer update --prefer-source

    exit
fi

composer self-update

composer require --no-update symfony/config:${SYMFONY_VERSION}
composer require --no-update symfony/dependency-injection:${SYMFONY_VERSION}
composer require --no-update symfony/framework-bundle:${SYMFONY_VERSION}
composer require --no-update --dev symfony/yaml:${SYMFONY_VERSION}

composer remove --no-update --dev friendsofphp/php-cs-fixer

if [[ "$SYMFONY_VERSION" = *dev* ]]; then
    composer config minimum-stability dev
fi

composer update --prefer-source `if [ "$COMPOSER_PREFER_LOWEST" = true ]; then echo "--prefer-lowest --prefer-stable"; fi`
