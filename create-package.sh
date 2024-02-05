#!/bin/bash

dontenv_key=$1

echo "* Generate package ID"
package_id=$(LC_CTYPE=C tr -dc A-Za-z0-9 < /dev/urandom | head -c 32 | xargs)
echo "package_id=$package_id" >> $GITHUB_OUTPUT
echo "  Package ID: $package_id"
echo "  Done"


echo "* Preparing on-site installer scripts for deployment"
mkdir -p ./installer/$package_id
touch ./installer/index.php
echo "<?php /* Silence is golden */ ?>" > ./installer/index.php
cp ./repos/sytesbook-wpwedding-deploy/installer/* ./installer/$package_id
echo "  Done"


echo "* Removing unnecessary files"
rm -r ./repos/sytesbook-wpwedding/wp/wp-content
rm -r ./repos/sytesbook-wpwedding/src/wp-content/themes/sytesbook-wpwedding/node_modules
rm ./repos/sytesbook-wpwedding/wp/composer.json
rm ./repos/sytesbook-wpwedding/wp/license.txt
rm ./repos/sytesbook-wpwedding/wp/readme.html
rm ./repos/sytesbook-wpwedding/wp/wp-config.php
echo "  Done"


echo "* Adding miscellaneous files"
ls -l ./repos/sytesbook-wpwedding-deploy/misc
cp ./repos/sytesbook-wpwedding-deploy/misc/.htaccess ./repos/sytesbook-wpwedding/wp
echo "  Done"


echo "* Creating version.json"
cd ./repos/sytesbook-wpwedding
touch version.json
echo "{" >> version.json
echo "  \"revision\": \"$(git rev-parse HEAD)\"," >> version.json
echo "  \"branch\": \"$(git rev-parse --abbrev-ref HEAD)\"" >> version.json
echo "}" >> version.json
echo "$(cat version.json)"
echo "  Done"


echo "* Decrypting .env.vault"
node ../sytesbook-wpwedding-deploy/decrypt-env.js .env.vault .env
echo "  Done"


echo "* Creating ZIP file for deployment"
mkdir ../../package
zip -r "../../package/package_$package_id.zip" src vendor wp .env version.json
echo "  Package created"
