#!/bin/bash

# echo "* Removing unnecessary files and symbolic links created by composer"
# rm -r ./repos/sytesbook-wpwedding/wp/uploads
# rm -r ./repos/sytesbook-wpwedding/wp/wp-content
# rm -r ./repos/sytesbook-wpwedding/src/wp-content/themes/sytesbook-wpwedding/node_modules
# rm ./repos/sytesbook-wpwedding/wp/composer.json
# rm ./repos/sytesbook-wpwedding/wp/license.txt
# rm ./repos/sytesbook-wpwedding/wp/readme.html
# rm ./repos/sytesbook-wpwedding/wp/wp-config.php
# echo "  Done"


# echo "* Adding miscellaneous files"
# cp ./repos/sytesbook-wpwedding-deploy/misc/.htaccess ./repos/sytesbook-wpwedding/wp
# echo "  Done"


# echo "* Creating version.json"
# cd ./repos/sytesbook-wpwedding
# touch version.json
# echo "{" >> version.json
# echo "  \"revision\": \"$(git rev-parse HEAD)\"," >> version.json
# echo "  \"branch\": \"$(git rev-parse --abbrev-ref HEAD)\"" >> version.json
# echo "}" >> version.json
# echo "$(cat version.json)"
# cd ../..
# echo "  Done"


echo "* Decrypting .env.$ENVIRONMENT"
cat ./repos/sytesbook-wpwedding/.env.$ENVIRONMENT > ./repos/sytesbook-wpwedding/.env
if ! [[ $? -eq 0 ]]; then
    echo "Failed to copy .env.$ENVIRONMENT to .env"
    exit 1
fi
npx dotenvx decrypt -f ./repos/sytesbook-wpwedding/.env
echo "  Done"


# echo "* Creating tar.gz archive for deployment"
# cd ./repos/sytesbook-wpwedding
# tar -czf ../../package.tar.gz src vendor wp .env version.json
# cd ../..
# echo "  Package created"
