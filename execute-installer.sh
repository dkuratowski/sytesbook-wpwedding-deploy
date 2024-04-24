#!/bin/bash

installer_script=$1
domain_name=$2
package_id=$3
deployment_folder=$4
domain_folder=$5


echo "Execute on-site install script"
mapfile output < <(curl --silent --write-out "\n%{response_code}" "https://$domain_name/installer/$package_id/execute.php?script=$installer_script&deployment_folder=$deployment_folder&domain_folder=$domain_folder")
output_length=${#output[@]}
response_code="${output[$(($output_length - 1))]}"
last_line_index=$(($output_length - 2))
if [[ $response_code != "200" ]]; then
    echo "  Failed"
    echo "  Output:"
    for (( i=0; i<=$last_line_index; i++))
    do
        echo "    ${output[$i]}"
    done
    exit 1
fi
echo "  Done"
echo "  Output:"
for (( i=0; i<=$last_line_index; i++))
do
    echo "    ${output[$i]}"
done

exit 0
