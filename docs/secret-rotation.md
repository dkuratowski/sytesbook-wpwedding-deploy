# Secret Rotation Process
This document contains a detailed description about how to rotate all the secrets. The steps below shall be performed for each environment `{env}` (where the given step is applicable).
We are using [Dotenv Vault](https://vault.dotenv.org/account/login) for central secret management. Secrets are decoded during deployment time and stored in the `/var/www/{domain-name}/deployment/.env` files on `staging` and `production` environments. Therefore, we have to update the secrets first in the deployed `.env` files in order to avoid downtime on the live environment, then in Dotenv Vault so that next deployments can also use the updated secrets.

## WordPress Secret Keys
* Copy the current value of the `AUTH_KEY` environment variable to `AUTH_KEY_{n}` where 0 <= `n` < 10 is the lowest number such that `AUTH_KEY_{n}` does not exist
* Regenerate WordPress secret keys [here](https://api.wordpress.org/secret-key/1.1/salt/)
* Copy the generated values to the `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT` and `NONCE_SALT` environment variables respectively.

## Database Password
* Connect to DB as root: `mysql`
* Switch to the appropriate database: `USE db_wedding_{env}_sytesbook;`
* Regenerate the password of the appropriate user: `ALTER USER usr_wedding_{env}_sytesbook IDENTIFIED BY RANDOM PASSWORD;`
* Save the generated password to the `DB_PASSWORD` environment secret.
* Exit from MySQL: `exit`

Test if new user has proper permissions:
* Login to MySQL console by the user: `mysql -u usr_wedding_{env}_sytesbook -p`
* Enter the `{password}` that was given to the new user.
* Query: `SHOW DATABASES;`
* You should see `db_wedding_{env}_sytesbook`, `information_schema` and `performance_schema` listed.
* Exit from MySQL: `exit`

## BetterStack Token

## Mailtrap (only for `development` environment)
* Go to settings of [`My Inbox`](https://mailtrap.io/inboxes/2433811/messages) and press `Reset Credentials`
* Copy the updated credentials to the `SMTP_USER` and `SMTP_PASSWORD` environment variables

## AWS Access Keys and Secret Access Keys
### `{env}.data-backup-wedding-sytesbook-com-s3-user` (only for `staging` and `production` environments)
* Go to the [IAM Users page](https://us-east-1.console.aws.amazon.com/iam/home?region=eu-central-1#/users) and select the `{env}.data-backup-wedding-sytesbook-com-s3-user` user.
* Move to the `Security Credentials` tab
* Create a new Access Key
* Copy the updated credentials to the `S3_BACKUP_ACCESS_KEY` and `S3_BACKUP_SECRET_ACCESS_KEY` environment variables
* Deactivate / delete the old access key

### `{env}.ses-smtp-user` (only for `staging` and `production` environments)
* Go to the [IAM Users page](https://us-east-1.console.aws.amazon.com/iam/home?region=eu-central-1#/users) and select the `{env}.ses-smtp-user` user.
* Move to the `Security Credentials` tab
* Create a new Access Key
* Copy the updated credentials to the `SMTP_USER` and `SMTP_PASSWORD` environment variables
* Deactivate / delete the old access key

## SSH Keys for `deploy-{env}` user
Generate new SSH keypair for user `deploy-{env}` on the local machine:
* `ssh-keygen -t ed25519 -C 'SSH key for deploy-{env} on {server-name}'`

Upload the generated SSH public key for user `deploy-{env}` to the server:
* `ssh-copy-id -i .ssh_sytesbook/deploy-{env}.pub deploy-{env}@{ip-address}`

Add the generated SSH private key to the environment secrets:
* Go to the [environment settings page](https://github.com/dkuratowski/sytesbook-wpwedding-deploy/settings/environments) of the `dkuratowski/sytesbook-wpwedding-deploy` repository
* Open the appropriate environment and update the `SSH_KEY` environment secret

## Dotenv Vault Key
* Follow the instructions [here](https://www.dotenv.org/docs/dotenv-vault/rotatekey)
* Go to the [environment settings page](https://github.com/dkuratowski/sytesbook-wpwedding-deploy/settings/environments) of the `dkuratowski/sytesbook-wpwedding-deploy` repository
* Open the appropriate environment and update the `DOTENV_VAULT_KEY` environment secret

## Access Token for Deployment GitHub Action
* Go to the [Personal Access Tokens page](https://github.com/settings/personal-access-tokens) on GitHub
* Open and regenerate token `sytesbook-wpwedding-deploy-{env}` and copy it to the clipboard
* Go to the [environment settings page](https://github.com/dkuratowski/sytesbook-wpwedding-deploy/settings/environments) of the `dkuratowski/sytesbook-wpwedding-deploy` repository
* Open the appropriate environment and update the `GIT_ACCESS_TOKEN` environment secret
