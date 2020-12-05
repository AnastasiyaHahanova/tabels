#!/bin/sh

Fail() { echo "ERROR: $@" 1>&2; exit 1; }

for c in php ; do
  which $c >/dev/null 2>&1 || Fail "$c not found"
done

# This script will stop when one of the commands returns with a non-zero value
set -ex pipefail

# Need to change directory to one level up from current script location
cd "$(cd "$(dirname "$0")" && pwd)/.."

. bin/root-blocker.sh

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

for f in  'tests/PhpUnit/config.yml' 'config/parameters/forbidden_domains.yaml' 'config/parameters/project_defaults.yaml' 'config/parameters/allowed_cnames.yaml' 'config/parameters/allowed_ips.yaml'; do
  if [ ! -f $f ] ; then
    cp $f.dist $f
    echo "File created from $f"
  fi
done

php bin/console cache:clear --env=prod --no-debug || rm -rf var/cache/prod
php bin/console cache:clear --env=prod_cli --no-debug || rm -rf var/cache/prod_cli