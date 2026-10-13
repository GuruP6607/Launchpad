#!/bin/sh

PROJECT="/home/workspace/my-project/internship-portal"
EXT="/home/workspace/my-project/php-mysql-extracted/usr/lib/php/20240924"

cd "$PROJECT" || exit 1

exec php \
  -d extension="$EXT/mysqlnd.so" \
  -d extension="$EXT/pdo_mysql.so" \
  -d extension="$EXT/mysqli.so" \
  -S 0.0.0.0:8080 \
  -t .
