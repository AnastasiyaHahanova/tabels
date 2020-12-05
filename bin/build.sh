#!/bin/sh
# Use global composer if exists
if which composer >/dev/null 2>&1; then
  composer config -g github-oauth.github.com 039ab9ce113a2639216e82b9f5e123a2a722fb01
  composer install --prefer-dist --no-interaction
  composer dump-autoload --optimize --classmap-authoritative
else
  test -e "composer.phar" && php composer.phar self-update || php -r "readfile('https://getcomposer.org/installer');" | php
  php composer.phar config -g github-oauth.github.com 039ab9ce113a2639216e82b9f5e123a2a722fb01
  php composer.phar install --prefer-dist --no-interaction
  php composer.phar dump-autoload --optimize --classmap-authoritative
fi

php bin/console cache:clear --env=prod --no-debug || rm -rf var/cache/prod
php bin/console cache:clear --env=prod_cli --no-debug || rm -rf var/cache/prod_cli