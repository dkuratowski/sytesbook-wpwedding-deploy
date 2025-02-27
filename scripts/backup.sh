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

backup_directory=$(date '+%Y%m%d_%H%M%S')

(
    # Removing previous backups
    echo 'Removing previous backups'
    rm -rfv ./backups/*

    # Creating backup directory
    echo "Creating backup directory ./backups/$backup_directory"
    mkdir ./backups/$backup_directory

    # Creating database dump
    echo "Creating database dump ./backups/$backup_directory/dbdump.gz"
    ./deployment/vendor/wp-cli/wp-cli/bin/wp db export --path=./deployment/wp - | gzip > ./backups/$backup_directory/dbdump.gz
    if ! [[ $? -eq 0 ]]; then
        echo "Failed to create database dump ./backups/$backup_directory/dbdump.gz"
        exit 1
    fi

    # Creating uploads archive
    echo "Creating uploads archive ./backups/$backup_directory/uploads.tar.gz"
    cd ./uploads && tar -czf ../backups/$backup_directory/uploads.tar.gz * && cd ..
    if ! [[ $? -eq 0 ]]; then
        echo "Failed to create uploads archive ./backups/$backup_directory/uploads.tar.gz"
        exit 1
    fi

    # Loading the environment variables needed for the backup script
    source ./deployment/.env
    export AWS_ACCESS_KEY_ID="$S3_BACKUP_ACCESS_KEY"
    export AWS_SECRET_ACCESS_KEY="$S3_BACKUP_SECRET_ACCESS_KEY"
    export AWS_DEFAULT_REGION="$S3_BACKUP_REGION"
    if ! [[ $? -eq 0 ]]; then
        echo "Failed to load AWS credentials and other environment variables"
        exit 1
    fi

    # Uploading database dump to S3
    echo "Uploading database dump to S3: ./backups/$backup_directory/dbdump.gz -> s3://$S3_BACKUP_BUCKET_NAME/$S3_BACKUP_OBJECT_BASE_PATH/$backup_directory/dbdump.gz"
    aws s3 cp ./backups/$backup_directory/dbdump.gz s3://$S3_BACKUP_BUCKET_NAME/$S3_BACKUP_OBJECT_BASE_PATH/$backup_directory/dbdump.gz --storage-class GLACIER
    if ! [[ $? -eq 0 ]]; then
        echo "Failed to upload database dump to S3: ./backups/$backup_directory/dbdump.gz -> s3://$S3_BACKUP_BUCKET_NAME/$S3_BACKUP_OBJECT_BASE_PATH/$backup_directory/dbdump.gz"

        echo "Notify the heartbeat URL about the failure"
        curl "$S3_BACKUP_FAILURE_HEARTBEAT_URL"
        if ! [[ $? -eq 0 ]]; then
            echo "Failed to notify the heartbeat URL about the failure"
        fi
        exit 1
    fi

    # Uploading uploads archive to S3
    echo "Uploading uploads archive to S3: ./backups/$backup_directory/uploads.tar.gz -> s3://$S3_BACKUP_BUCKET_NAME/$S3_BACKUP_OBJECT_BASE_PATH/$backup_directory/uploads.tar.gz"
    aws s3 cp ./backups/$backup_directory/uploads.tar.gz s3://$S3_BACKUP_BUCKET_NAME/$S3_BACKUP_OBJECT_BASE_PATH/$backup_directory/uploads.tar.gz --storage-class GLACIER
    if ! [[ $? -eq 0 ]]; then
        echo "Failed to upload uploads archive to S3: ./backups/$backup_directory/uploads.tar.gz -> s3://$S3_BACKUP_BUCKET_NAME/$S3_BACKUP_OBJECT_BASE_PATH/$backup_directory/uploads.tar.gz"

        echo "Notify the heartbeat URL about the failure"
        curl "$S3_BACKUP_FAILURE_HEARTBEAT_URL"
        if ! [[ $? -eq 0 ]]; then
            echo "Failed to notify the heartbeat URL about the failure"
        fi
        exit 1
    fi

    echo "Notify the heartbeat URL about the success"
    curl "$S3_BACKUP_SUCCESS_HEARTBEAT_URL"
    if ! [[ $? -eq 0 ]]; then
        echo "Failed to notify the heartbeat URL about the success"
        exit 1
    fi
) 2>&1 | tee "./backups/$backup_directory.log"
