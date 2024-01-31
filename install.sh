#!/bin/bash

domain_name=$1
deployment_id=$2
deployment_folder=$3
domain_folder=$4

# echo "Unzipping deployed files"
# mapfile output < <(curl --silent --write-out "\n%{response_code}" "https://$domain_name/installer/$deployment_id/unzip.php?deployment_folder=$deployment_folder")
# echo ${output[@]}

echo "Creating symbolic links"
mapfile output < <(curl --silent --write-out "\n%{response_code}" "https://$domain_name/installer/$deployment_id/symlink.php?deployment_folder=$deployment_folder&domain_folder=$domain_folder")
echo ${output[@]}

exit 0
