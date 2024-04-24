#!/bin/bash

dontenv_key=$1

echo "* Generate package ID"
package_id=$(LC_CTYPE=C tr -dc A-Za-z0-9 < /dev/urandom | head -c 32 | xargs)
echo "package_id=$package_id" >> $GITHUB_OUTPUT
echo "  Package ID: $package_id"
echo "  Done"


echo "* Removing unnecessary files and symbolic links created by composer"
rm -r ./repos/sytesbook-wpwedding/wp/migrations
rm -r ./repos/sytesbook-wpwedding/wp/uploads
rm -r ./repos/sytesbook-wpwedding/wp/wp-content
rm -r ./repos/sytesbook-wpwedding/src/wp-content/themes/sytesbook-wpwedding/node_modules
rm ./repos/sytesbook-wpwedding/wp/composer.json
rm ./repos/sytesbook-wpwedding/wp/license.txt
rm ./repos/sytesbook-wpwedding/wp/readme.html
rm ./repos/sytesbook-wpwedding/wp/wp-config.php
echo "  Done"


echo "* Adding miscellaneous files"
cp ./repos/sytesbook-wpwedding-deploy/misc/.htaccess ./repos/sytesbook-wpwedding/wp
echo "  Done"


echo "* Creating version.json"
touch ./repos/sytesbook-wpwedding/version.json
echo "{" >> version.json
echo "  \"revision\": \"$(git rev-parse HEAD)\"," >> ./repos/sytesbook-wpwedding/version.json
echo "  \"branch\": \"$(git rev-parse --abbrev-ref HEAD)\"" >> ./repos/sytesbook-wpwedding/version.json
echo "}" >> ./repos/sytesbook-wpwedding/version.json
echo "$(cat ./repos/sytesbook-wpwedding/version.json)"
echo "  Done"


echo "* Decrypting .env.vault"
node ./repos/sytesbook-wpwedding-deploy/decrypt-env.js .env.vault .env
echo "  Done"


echo "* Creating ZIP file for deployment"
mkdir ./package
cd ./repos/sytesbook-wpwedding
zip -r "../../package/package_$package_id.zip" src vendor wp .env version.json
cd ../..
echo "  Package created"
