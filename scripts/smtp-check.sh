#!/bin/bash

# WARNING!
# This script shall be run from the environment root directory (e.g. /var/www/wedding.sytesbook.com)

if ! [[ -e ./deployment/.env ]]; then
    echo "./deployment/.env doesn't exist. The smtp-check script shall be run from the environment root directory"
    exit 1
fi

./deployment/vendor/wp-cli/wp-cli/bin/wp smtp check --path=./deployment/wp
