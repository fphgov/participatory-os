#!/bin/sh

unset MODE

while getopts 'm:' c
do
  case $c in
    m) MODE="$OPTARG" ;;
  esac
done

if [ $MODE = "development" ]; then
  cd /var/www/html

  php -d allow_url_fopen=on /usr/local/bin/composer install --ignore-platform-req=ext-postal

  composer db-update

  chmod 0644 bin/cron/notifications.php
fi

if [ $MODE = "production" ]; then
  cd /var/www/html

  php -d allow_url_fopen=on /usr/local/bin/composer install --ignore-platform-req=ext-postal --no-ansi --no-dev --no-interaction --no-progress --no-scripts --optimize-autoloader

  composer db-update

  chmod 0644 bin/cron/notifications.php
fi

mkdir -p data/cache/DoctrineEntityProxy
chown 1000:1000 -R /var/www/html/data/cache && chmod 777 -R data/cache/DoctrineEntityProxy

mkdir -p data/minio
chown 1001:1001 -R data/minio
