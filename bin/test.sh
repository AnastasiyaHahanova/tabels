#!/bin/sh

cp .env .env.test
php bin/console doctrine:database:drop --env=test --force --if-exists
php bin/console doctrine:database:create --env=test
php bin/console doctrine:schema:update --env=test --force --no-interaction

./bin/phpunit