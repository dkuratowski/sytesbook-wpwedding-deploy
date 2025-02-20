#!/bin/bash

# WARNING!
# This script shall be run from the environment root directory (e.g. /var/www/wedding.sytesbook.com)

if ! [[ -e ./backups ]]; then
    echo "./backups doesn't exist. The backup script shall be run from the environment root directory"
    exit 1
fi

if ! [[ -e ./uploads ]]; then
    echo "./uploads doesn't exist. The backup script shall be run from the environment root directory"
    exit 1
fi

if ! [[ -e ./deployment/.env ]]; then
    echo "./deployment/.env doesn't exist. The backup script shall be run from the environment root directory"
    exit 1
fi

# Removing previous backups
echo 'Removing previous backups'
rm -rfv ./backups/*

# Creating backup directory
export backup_directory=$(date '+%Y%m%d_%H%M%S')
echo "Creating backup directory ./backups/$backup_directory"
mkdir ./backups/$backup_directory

# Creating database dump
echo "Creating database dump ./backups/$backup_directory/dbdump.gz"
./deployment/vendor/wp-cli/wp-cli/bin/wp db export --path=./deployment/wp - | gzip > ./backups/$backup_directory/dbdump.gz

# Creating uploads archive
echo "Creating uploads archive ./backups/$backup_directory/uploads.tar.gz"
cd ./uploads && tar -czf ../backups/$backup_directory/uploads.tar.gz * && cd ..

# Loading the environment variables needed for the backup script
# source ./deployment/.env
# export AWS_ACCESS_KEY_ID="$S3_BACKUP_ACCESS_KEY"
# export AWS_SECRET_ACCESS_KEY="$S3_BACKUP_SECRET_ACCESS_KEY"
# export AWS_DEFAULT_REGION="$S3_BACKUP_REGION"
