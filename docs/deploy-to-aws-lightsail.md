# Deployment to AWS LightSail instance

## Create AWS LightSail instance
* Login to AWS Management Console
* At Amazon LightSail go to `Create instance`
* Select `Linux`, `OS only`, `Ubuntu 24.04 LTS`
* Select size and press `Create instance`
* Attach static IP address to the created server
* Open the following ports on the server: 22 (SSH), 80 (HTTP), 443 (HTTPS)
* Connect to the server via SSH from the browser console
* Run `apt update`
* Run `apt full-upgrade`
* Reboot the server

## Create Directory Structure
Create the directory structure on the server as depicted on the image below by blue boxes:
![Server Directory Structure](docs/resources/sytesbook.wpwedding.deploy-server-dir-struct.drawio.png)

## Setup Service Users, Groups & Permissions
Create the following service users for each environment `{env}`:
* `apache-{env}`: The corresponding Apache virtual host will be running as this user.
* `deploy-{env}`: The GitHub Actions workflow deploying to `{env}` and corresponding cronjobs will be running as this user.

Create the following group for each environment `{env}`:
* `sytesbook-{env}`: The group owner of all directories inside the environment root.

Add the created users to the corresponding `sytesbook-{env}` group.
  
Set file permissions and ownerships of the directories inside each environment root:
* `main-domain-public`:
  * owner: `deploy-{env}`
  * group owner: `sytesbook-{env}`
  * permissions for directories: `rwxr-s--- (2750)`
  * permissions for files: `rw-r----- (0640)`
* `model-domains-public`:
  * owner: `deploy-{env}`
  * group owner: `sytesbook-{env}`
  * permissions for directories: `rwxr-s--- (2750)`
  * permissions for files: `rw-r----- (0640)`
* `deployment`:
  * owner: `deploy-{env}`
  * group owner: `sytesbook-{env}`
  * permissions for directories: `rwxr-s--- (2750)`
  * permissions for files: `rw-r----- (0640)`
* `uploads`:
  * owner: `apache-{env}`
  * group owner: `sytesbook-{env}`
  * permissions for directories: `rwxr-s--- (2750)`
  * permissions for files: `rw-r----- (0640)`
* `logs`:
  * owner: `apache-{env}`
  * group owner: `sytesbook-{env}`
  * permissions for directories: `rwxr-s--- (2750)`
  * permissions for files: `rw-r----- (0640)`
* `scripts`:
  * owner: `deploy-{env}`
  * group owner: `sytesbook-{env}`
  * permissions for directories: `rwxr-s--- (2750)`
  * permissions for files: `rw-r----- (0640)`
* `backups`:
  * owner: `deploy-{env}`
  * group owner: `sytesbook-{env}`
  * permissions for directories: `rwxr-s--- (2750)`
  * permissions for files: `rw-r----- (0640)`

## Configure SSH
Temporarily enable password authentication on the server:
* Add the following line to `/etc/ssh/sshd_config.d/60-cloudimg-settings.conf` and `/etc/ssh/sshd_config`: `PasswordAuthentication yes` and `PubKeyAuthentication no`
* Restart SSH with `systemctl restart ssh`
* Setup password for users `deploy-{env}`: `passwd deploy-{env}`

Generate SSH keypair for user `deploy-{env}` on the local machine:
* `ssh-keygen -t ed25519 -C 'SSH key for deploy-{env} on {server-name}'`

Upload the generated SSH public key for user `deploy-{env}` to the server:
* `ssh-copy-id -i .ssh_sytesbook/deploy-{env}.pub deploy-{env}@{ip-address}`
* This will ask for password of deploy-{env}

Disable password authentication:
* Add the following line to `/etc/ssh/sshd_config.d/60-cloudimg-settings.conf` and `/etc/ssh/sshd_config`: `PasswordAuthentication no` and `PubKeyAuthentication yes`
* Restart SSH with `systemctl restart ssh`

## Setup UMASK for `deploy-{env}` and `apache-{env}` users
* Calculate the necessary `umask` value:
  * default file permissions: `666 = 110 110 110`
  * default directory permissions: `777 = 111 111 111`
  * umasked file permissions: `640 = 110 100 000`
  * umasked directory permissions: `750 = 111 101 000`
  * umask value: `027 = 000 010 111`

* Turn on `pam_umask` module by appending the following line to `/etc/pam.d/common-session` (if doesn't exist yet): `session optional pam_umask.so`

* Add UMASK value to the GECOS field of the users in `/etc/passwd`:
  * `deploy-{env}:x:1000:1000:,,,umask=0027:/home/deploy-{env}:/bin/bash
  * `apache-{env}:x:1001:1001:,,,umask=0027:/nonexistent:/usr/sbin/nologin

## Create a swap file
* `sudo fallocate -l [swapfile_size] [path_to_swapfile]` (should be ~equal to the size of RAM, e.g. 1G)
* `chmod 600 /swapfil`
* `mkswap /swapfile`
* `swapon /swapfile`
* Add an entry to the /etc/fstab: `/swapfile none swap sw 0 0`

## Install and Configure AWS CLI
Run the following commands as root:
* `curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"`
* `unzip awscliv2.zip` (install `unzip` if necessary with `apt install unzip`)
* `./aws/install`

## Install Apache Web Server
* Run `apt install apache2`
* Try to browse `http://{your-ip-address}`. It should show the Apache2 default page
* Install and enable `libapache2-mpm-itk` module to allow different users per VirtualHosts:
  * `apt install libapache2-mpm-itk`
  * `a2enmod libapache2-mpm-itk`
  * `a2enmod status`

## Install MySQL Server
* Run `apt install mysql-server`
* Run `mysql_secure_installation`. This script will remove some insecure default settings and lock down access to your database system.
* Try login to MySQL with `mysql`. You should see the MySQL prompt

## Install PHP
* Run `apt install php libapache2-mod-php php-mysql php-curl php-dom php-gd`
* Check PHP version with `php -v`

## Configure Apache Web Server
Create a new VirtualHost for each environment `{env}` at `/etc/apache2/sites-available/{domain-name}.conf` (where `{domain-name}` is the domain name corresponding to environment `{env}`) with the following contents:
```
<VirtualHost *:80>
    ServerName {domain-name}
    ServerAlias www.{domain-name}
    ServerAdmin contact@example.com
    DocumentRoot /var/www/{domain-name}/main-domain-public
    <IfModule mpm_itk_module>
        AssignUserId apache-{env} sytesbook-{env}
    </IfModule>
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

Enable the new VirtualHost: `a2ensite {domain-name}`

Create a new configuration at `/etc/apache2/sites-available/enable-rewrite.conf` with the following contents:
```
<Directory /var/www/>
    AllowOverride All
</Directory>
```

Enable the new configuration: `a2enconf enable-rewrite`

Restart Apache: `systemctl reload apache2`

## Configure SSL with Let's Encrypt
* Install `certbot`: `apt install certbot python3-certbot-apache`
* Run `certbot` as root: `certbot --apache`
* Follow instructions in the terminal

## Create WordPress databases
* Connect to DB as root: `mysql`
* Create new database for environment `{env}`: `CREATE DATABASE db_wedding_{env}_sytesbook;`
* Create user for the new database: `CREATE USER usr_wedding_{env}_sytesbook@localhost IDENTIFIED BY RANDOM PASSWORD;`
* Save the generated password to the `DB_PASSWORD` environment secret.
* Give the user permission over the new database: `GRANT ALL ON db_wedding_{env}_sytesbook.* TO usr_wedding_{env}_sytesbook@localhost;`
* Exit from MySQL: `exit`

Test if new user has proper permissions:
* Login to MySQL console by the new user: `mysql -u usr_wedding_{env}_sytesbook -p`
* Enter the `{password}` that was given to the new user.
* Query: `SHOW DATABASES;`
* You should see `db_wedding_{env}_sytesbook`, `information_schema` and `performance_schema` listed.
* Exit from MySQL: `exit`

## Install WordPress
* Open a browser and move to the domain name of the environment
* Follow the on-screen installation instructions
* Login to WordPress
* Activate the `Sytesbook WP-Wedding Theme` in the `Appearance/Themes` menu from the Admin Dashboard
* Open `Settings/Permalinks` page and press `Save Changes` button at the bottom of the screen

## Collect logs & metrics from Apache webserver
Create a new Source in Telemetry: `wedding.sytesbook.com/apache`

Install and configure Vector on Ubuntu or Debian:
```
curl -sSL https://telemetry.betterstack.com/setup-vector/apache/{source-token} -o /tmp/setup-vector.sh
bash /tmp/setup-vector.sh
```
