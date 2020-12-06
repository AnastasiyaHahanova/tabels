#!/usr/bin/env bash
#
# Build symfony application
#
# Copyright (c) 2020 by cdnnow! <cdnnow.pro>. All rights reserved.
#
# Written by Gleb Shchavlev <g@cdnnow.pro>
#

# Change directory to one level up from current script location
cd "$(cd "$(dirname "$0")" && pwd)/.." # "

test "root" != "$(whoami)" || Fail "Script cannot be executed by root system user"

for c in php; do
  which $c >/dev/null 2>&1 || Fail "$c not found"
done

# This script will stop when one of the commands returns with a non-zero value
set -ex pipefail

rm -rf var/cache

# Use global composer if exists
if which composer >/dev/null 2>&1; then
  composer config -g github-oauth.github.com 039ab9ce113a2639216e82b9f5e123a2a722fb01
  composer install --prefer-dist --no-interaction
  composer dump-autoload --optimize --classmap-authoritative
else
  test -e "composer.phar" || php -r "readfile('https://getcomposer.org/installer');" | php
  php composer.phar config -g github-oauth.github.com 039ab9ce113a2639216e82b9f5e123a2a722fb01
  php composer.phar self-update
  php composer.phar install --prefer-dist --no-interaction
  php composer.phar dump-autoload --optimize --classmap-authoritative
fi
