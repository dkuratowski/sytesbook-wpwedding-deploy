#!/bin/bash

domain_name=$1
deployment_id=$2
deployment_folder=$3
domain_folder=$4

echo "Unzipping deployed files"
mapfile output < <(curl --silent --write-out "\n%{response_code}" "https://$domain_name/installer/$deployment_id/unzip.php?deployment_folder=$deployment_folder")
output_length=${#output[@]}
response_code="${output[$(($output_length - 1))]}"
last_line_index=$(($output_length - 2))
if [[ $response_code != "200" ]]; then
    echo "Failed"
    for (( i=0; i<=$last_line_index; i++))
    do
        echo "${output[$i]}";
    done
    exit 1
fi
echo "OK"

echo "Creating symbolic links"
mapfile output < <(curl --silent --write-out "\n%{response_code}" "https://$domain_name/installer/$deployment_id/symlink.php?deployment_folder=$deployment_folder&domain_folder=$domain_folder")
output_length=${#output[@]}
response_code="${output[$(($output_length - 1))]}"
last_line_index=$(($output_length - 2))
if [[ $response_code != "200" ]]; then
    echo "Failed"
    for (( i=0; i<=$last_line_index; i++))
    do
        echo "${output[$i]}";
    done
    exit 1
fi

echo "OK"
exit 0
