#!/bin/sh

sh bin/build.sh

php bin/console doctrine:database:create --if-not-exists

php bin/console doctrine:migrations:migrate