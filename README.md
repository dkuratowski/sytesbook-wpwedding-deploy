# Sytesbook WPWedding - Deploy
## GitHub Actions Folder Structure
`repos/sytesbook-wpwedding` - Run `composer install`

`repos/sytesbook-wpwedding/wp` - Zip to `deploy/wp.zip`

`repos/sytesbook-wpwedding/src` - Zip to `deploy/wp_src.zip`

`repos/sytesbook-wpwedding/vendor` - Zip to `deploy/wp_src.zip`

`repos/sytesbook-wpwedding/wp/wp-content/themes/sytesbook-wpwedding` - Run `npm install` and `npm run build`

`repos/sytesbook-wpwedding-installer` - Required for `composer install`

`repos/sytes-react` - Required for `npm install` and `npm run build`

`deploy/wp.zip` - Upload and unzip to the public folder on the webserver

`deploy/wp_src.zip` - Upload and unzip to the private folder on the webserver
