#!/bin/sh
set -e

yarn install || exit $?

if [ "$VS_ENV" = 'production' ]; then
  yarn build || exit $?
  yarn start
fi
