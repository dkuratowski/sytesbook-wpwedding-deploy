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

## Dotenv Vault Key
* Run: `npx dotenv-vault rotatekey {env}`
  * The new decryption key is printed to the console
* Run: `npx dotenv-vault build`
* Commit and push `.env.vault` to the repository
* Go to the [environment settings page](https://github.com/dkuratowski/sytesbook-wpwedding-deploy/settings/environments) of the `dkuratowski/sytesbook-wpwedding-deploy` repository
* Open the appropriate environment and update the `DOTENV_VAULT_KEY` environment secret with the new decryption key

## Access Token for Deployment GitHub Action
* Go to the [Personal Access Tokens page](https://github.com/settings/personal-access-tokens) on GitHub
* Open and regenerate token `sytesbook-wpwedding-deploy-{env}` and copy it to the clipboard
* Go to the [environment settings page](https://github.com/dkuratowski/sytesbook-wpwedding-deploy/settings/environments) of the `dkuratowski/sytesbook-wpwedding-deploy` repository
* Open the appropriate environment and update the `GIT_ACCESS_TOKEN` environment secret

## WordPress Secret Keys
* Login to [Dotenv Vault](https://vault.dotenv.org/account/login)
* Regenerate WordPress secret keys [here](https://api.wordpress.org/secret-key/1.1/salt/) and update the appropriate environment
  * Copy the current value of the `AUTH_KEY` environment variable to `PREVIOUS_AUTH_KEY_{n}` where 0 <= `n` < 10 is the lowest number such that `PREVIOUS_AUTH_KEY_{n}` does not exist
  * Copy the generated values to the `AUTH_KEY`, `SECURE_AUTH_KEY`, `LOGGED_IN_KEY`, `NONCE_KEY`, `AUTH_SALT`, `SECURE_AUTH_SALT`, `LOGGED_IN_SALT` and `NONCE_SALT` environment variables respectively.
* Run: `npx dotenv-vault pull`
* Commit and push `.env.vault` to the repository
* Redeploy

## Database Password
* Connect to DB as root: `mysql`
* Switch to the appropriate database: `USE db_wedding_{env}_sytesbook;`
* Regenerate the password of the appropriate user: `ALTER USER 'usr_wedding_{env}_sytesbook'@'localhost' IDENTIFIED BY RANDOM PASSWORD;`
* Copy the new password to the clipboard
* Exit from MySQL: `exit`

Test if new user has proper permissions:
* Login to MySQL console by the user: `mysql -u usr_wedding_{env}_sytesbook -p`
* Enter the new `{password}` when prompted
* Query: `SHOW DATABASES;`
* You should see `db_wedding_{env}_sytesbook`, `information_schema` and `performance_schema` listed.
* Exit from MySQL: `exit`

Update the new DB password to Dotenv Vault:
* Login to [Dotenv Vault](https://vault.dotenv.org/account/login)
* Open the appropriate environment
* Update the `DB_PASSWORD` environment secret
* Run: `npx dotenv-vault pull`
* Commit and push `.env.vault` to the repository
* Redeploy

## BetterStack Token (only applicable for `staging` and `production` environments)
Note: It is not possible to rotate at the moment. Source needs to be recreated.
* Go to [BetterStack sources](https://telemetry.betterstack.com/team/158180/sources)
* Rename current log sources (e.g. append `/archived` to their names)
* Create new log sources
* Copy source token and ingesting host to the clipboard
* Login to [Dotenv Vault](https://vault.dotenv.org/account/login)
* Open the appropriate environment
* Update the `BETTERSTACK_TOKEN` and `BETTERSTACK_INGESTING_HOST` environment secrets with the new values saved to the clipboard
* Run: `npx dotenv-vault pull`
* Commit and push `.env.vault` to the repository
* Redeploy
* Update the sources of the dashboards to the new log sources
* Recreate the `response_status` metric (JSON source: monolog.context.response.status, type: Int64, aggregation: no aggregation) and add it to the new log sources

## AWS Access Key for Backup User (only applicable for `staging` and `production` environments)
* Go to the [IAM Users page](https://us-east-1.console.aws.amazon.com/iam/home?region=eu-central-1#/users) and select the `{env}.data-backup-wedding-sytesbook-com-s3-user` user.
* Move to the `Security Credentials` tab
* Create a new Access Key and copy the credentials to the clipboard
* Login to [Dotenv Vault](https://vault.dotenv.org/account/login)
* Open the appropriate environment
* Update the `S3_BACKUP_ACCESS_KEY` and `S3_BACKUP_SECRET_ACCESS_KEY` environment secrets with the new credentials
* Run: `npx dotenv-vault pull`
* Commit and push `.env.vault` to the repository
* Redeploy
* Deactivate / delete the old access key

## SMTP credentials on `development` environment (Mailtrap)
* Go to settings of [`My Inbox`](https://mailtrap.io/inboxes/2433811/messages) and press `Reset Credentials`
* Copy the new SMTP credentials to the clipboard
* Login to [Dotenv Vault](https://vault.dotenv.org/account/login)
* Open the `development` environment
* Update the `SMTP_USER` and `SMTP_PASSWORD` environment secrets with the new SMTP credentials
* Run: `npx dotenv-vault pull`
* Commit and push `.env.vault` to the repository

### SMTP credentials on `staging` and `production` environments (AWS SES)
* Go to the [IAM page](https://us-east-1.console.aws.amazon.com/iam/home?region=eu-central-1#/users) and delete the `{env}.ses-smtp-user` user.
* Go to the [AWS SES page](https://eu-central-1.console.aws.amazon.com/ses/home?region=eu-central-1#/account)
* Go to `SMTP settings` and click on `Create SMTP credentials`
* On the `Create user for SMTP` section set the user name to `{env}.ses-smtp-user` and click on `Create user`
* Copy the new SMTP credentials to the clipboard
* Remove the policy from the created user that is added by default
* Remove the user group that is created by default
* Attach the `{env}.ses-send-email-policy` policy directly to the created user
* Login to [Dotenv Vault](https://vault.dotenv.org/account/login)
* Open the appropriate environment
* Update the `SMTP_USER` and `SMTP_PASSWORD` environment secrets with the new SMTP credentials
* Run: `npx dotenv-vault pull`
* Commit and push `.env.vault` to the repository
* Redeploy
