#!/bin/bash

package_id=$1

mkdir -p ./installer/$package_id
touch ./installer/index.php
echo "<?php /* Silence is golden */ ?>" > ./installer/index.php
cd ./repos/sytesbook-wpwedding-deploy/installer
zip -r ../../../installer/$package_id/installer.zip Deploy scripts vendor
cd ../../..
cp ./repos/sytesbook-wpwedding-deploy/installer/execute.php ./installer/$package_id
