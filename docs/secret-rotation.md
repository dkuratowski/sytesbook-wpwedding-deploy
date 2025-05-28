# Secret Rotation Process
This document contains a detailed description about how to rotate all the secrets. The steps below shall be performed for each environment `{env}` (where the given step is applicable).

## SSH Keys for `deploy-{env}` user
Generate new SSH keypair for user `deploy-{env}` on the local machine:
* Rename old key files for user `deploy-{env}` on the local machine in the `~/.ssh_sytesbook` folder to `deploy-{env}-old` and `deploy-{env}-old.pub`
* `ssh-keygen -t ed25519 -C 'deploy-{env}@{server-name} {yyyymmdd}'`
  * Set keypair location to ~/.ssh_sytesbook
  * Set keypair name to `deploy-{env}`

Update the SSH public key for user `deploy-{env}` on the server:
* Add the new public key to the server:
  * SSH into the server with the old private key: `ssh -i ~/.ssh_sytesbook/deploy-{env}-old deploy-{env}@{ip-address}`
  * Add the new public key to `~/.ssh/authorized_keys`
* Remove the old public key from the server:
  * SSH into the server with the new private key: `ssh -i ~/.ssh_sytesbook/deploy-{env} deploy-{env}@{ip-address}`
  * Remove the old public key from `~/.ssh/authorized_keys`
* Check if the old private key can no longer be used to SSH into the server:
  * Try `ssh -i ~/.ssh_sytesbook/deploy-{env}-old deploy-{env}@{ip-address}`
  * The server shall reject the connection.

Add the new SSH private key to the environment secrets:
* Go to the [environment settings page](https://github.com/dkuratowski/sytesbook-wpwedding-deploy/settings/environments) of the `dkuratowski/sytesbook-wpwedding-deploy` repository
* Open the appropriate environment and update the `SSH_KEY` environment secret

## DotenvX keypair
* Backup your `.env.keys` file locally
* Run `npx dotenvx rotate -f .env.{env}`
* A new decryption key for environment `{env}` added to your `.env.keys` file
* Remove the old decryption key for environment `{env}` from your `.env.keys` file
* Commit and push `.env.{env}` to the repository
* Go to the [environment settings page](https://github.com/dkuratowski/sytesbook-wpwedding-deploy/settings/environments) of the `dkuratowski/sytesbook-wpwedding-deploy` repository
* Open the appropriate environment and update the `DOTENV_PRIVATE_KEY` environment secret with the new decryption key
* Redeploy

## Access Token for Deployment GitHub Action
* Go to the [Personal Access Tokens page](https://github.com/settings/personal-access-tokens) on GitHub
* Open and regenerate token `sytesbook-wpwedding-deploy-{env}` and copy it to the clipboard
* Go to the [environment settings page](https://github.com/dkuratowski/sytesbook-wpwedding-deploy/settings/environments) of the `dkuratowski/sytesbook-wpwedding-deploy` repository
* Open the appropriate environment and update the `GIT_ACCESS_TOKEN` environment secret

## WordPress Secret Keys
* Decrypt the appropriate `.env.{env}` file: `npx dotenvx decrypt -f .env.{env}`
* Generate a new WordPress secret keys (64 characters recommended). In order not to mass up the .env files prefer to use only lower and upper-case letters and numeric digits.
* Update the appropriate environment:
  * Copy the current value of the `AUTH_KEY` environment variable to `DEPRECATED_AUTH_KEY_{n}` where 0 <= `n` < 10 is the lowest number such that `DEPRECATED_AUTH_KEY_{n}` does not exist
  * Copy the generated values to the `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT` and `NONCE_SALT` environment variables respectively.
* Encrypt the appropriate `.env.{env}` file: `npx dotenvx encrypt -f .env.{env}`
* Commit and push `.env.{env}` to the repository
* Redeploy

## Database Password
* Generate a new random DB password (64 characters recommended). In order not to mass up the .env files prefer to use only the following characters: `abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-+*/=_@&%?`
* Connect to DB as root: `mysql`
* Switch to the appropriate database: `USE db_wedding_{env}_sytesbook;`
* Regenerate the password of the appropriate user: `ALTER USER 'usr_wedding_{env}_sytesbook'@'localhost' IDENTIFIED BY '{new-password}';`
* Reload privileges: `FLUSH PRIVILEGES;`
* Exit from MySQL: `exit`

Test if new user has proper permissions:
* Login to MySQL console by the user: `mysql -u usr_wedding_{env}_sytesbook -p`
* Enter the new password when prompted
* Query: `SHOW DATABASES;`
* You should see `db_wedding_{env}_sytesbook`, `information_schema` and `performance_schema` listed.
* Exit from MySQL: `exit`

Update the DB password in the appropriate `.env.{env}` file:
* Decrypt the appropriate `.env.{env}` file: `npx dotenvx decrypt -f .env.{env}`
* Copy the new DB password to the `DB_PASSWORD` environment variable
* Encrypt the appropriate `.env.{env}` file: `npx dotenvx encrypt -f .env.{env}`
* Commit and push `.env.{env}` to the repository
* Redeploy

## BetterStack Token (only applicable for `staging` and `production` environments)
Note: It is not possible to rotate at the moment. Source needs to be recreated.
* Go to [BetterStack sources](https://telemetry.betterstack.com/team/158180/sources)
* Rename current log sources (e.g. append `/archived` to their names)
* Create new log sources
* Decrypt the appropriate `.env.{env}` file: `npx dotenvx decrypt -f .env.{env}`
* Update the `BETTERSTACK_TOKEN` and `BETTERSTACK_INGESTING_HOST` environment variables
* Encrypt the appropriate `.env.{env}` file: `npx dotenvx encrypt -f .env.{env}`
* Commit and push `.env.{env}` to the repository
* Redeploy
* Update the sources of the dashboards to the new log sources
* Recreate the `response_status` metric (JSON source: monolog.context.response.status, type: Int64, aggregation: no aggregation) and add it to the new log sources

## AWS Access Key for Backup User (only applicable for `staging` and `production` environments)
* Go to the [IAM Users page](https://us-east-1.console.aws.amazon.com/iam/home?region=eu-central-1#/users) and select the `{env}.data-backup-wedding-sytesbook-com-s3-user` user.
* Move to the `Security Credentials` tab
* Create a new Access Key
* Decrypt the appropriate `.env.{env}` file: `npx dotenvx decrypt -f .env.{env}`
* Update the `S3_BACKUP_ACCESS_KEY` and `S3_BACKUP_SECRET_ACCESS_KEY` environment variables
* Encrypt the appropriate `.env.{env}` file: `npx dotenvx encrypt -f .env.{env}`
* Commit and push `.env.{env}` to the repository
* Redeploy
* Deactivate / delete the old access key

## SMTP credentials on `development` environment (Mailtrap Sandbox)
* Go to the SMTP settings of [`Fake Inbox`](https://mailtrap.io/inboxes/2433811/messages) and press `Reset Credentials`
* Copy the new SMTP credentials
* Decrypt the appropriate `.env.{env}` file: `npx dotenvx decrypt -f .env.{env}`
* Update the `SMTP_USER` and `SMTP_PASSWORD` environment variables
* Encrypt the appropriate `.env.{env}` file: `npx dotenvx encrypt -f .env.{env}`
* Commit and push `.env.{env}` to the repository
* Redeploy

### SMTP credentials on `staging` and `production` environments (Mailtrap Live)
* Go to the SMTP settings of the [`sytesbook.com`](https://mailtrap.io/sending/domains/8ce62c26-3e9e-4403-a095-b2d7ea6513aa?current_tab=smtp_settings&stream=transactional) domain and press `Reset Credentials`
* Copy the new SMTP credentials
* Decrypt the appropriate `.env.{env}` file: `npx dotenvx decrypt -f .env.{env}`
* Update the `SMTP_USER` and `SMTP_PASSWORD` environment variables
* Encrypt the appropriate `.env.{env}` file: `npx dotenvx encrypt -f .env.{env}`
* Commit and push `.env.{env}` to the repository
* Redeploy
