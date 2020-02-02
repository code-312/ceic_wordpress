# ceic_wordpress

Repository for work related to the Cannabis Equity Illinois Coalition Website

## Technology Stack

Docker Wordpress container - will pull latest based on YAML file.

### Notes

#### Docker Install

Make sure you have install docker using the instructions for your operating system at the [Docker website](https://docs.docker.com/get-started/#install-docker-desktop).

Once you have docker installed, run the following commands to clone the project to your local machine:

```bash
git clone https://github.com/Code-For-Chicago/ceic_wordpress.git
cd ceic_wordpress
docker-compose up -d
cat seed.sql | docker exec -i $(docker-compose ps -q db) mysql -u wordpress -pwordpress wordpress --init-command="SET autocommit=0;"
```

#### Github WP Sync

* ! TODO - initially and periodically check .gitignore to ensure that it's properly scoped - i.e. wp-content directory - currently looks good, but we want to maybe keep an eye out for DB seeding / raking (also per Ryan a docker config / init script) as well as php settings (default docker / WP may not be ideal - usu in /etc/php7.-4 but I don't see that in the docker image)
* There is an 'export' and 'import' built into WP which handles the associated content (pages, posts, and media) - wp-admin/import.php and export.php - 
* Currently the default user and password for both wordpress and mysql is exposed-- may want to use a secrets file.
  * This is also important for the init script in the [docker install](#docker-install) section (runs a command using the username and password in the shell)
