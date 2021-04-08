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
cat data/seed.sql | docker exec -i $(docker-compose ps -q db) mysql -u wordpress -pwordpress wordpress --init-command="SET autocommit=0;"
```

The `seed.sql` import above is an example of how one might import database information into your local development environment. Our intention is not to commit database dumps from upstream instances of this site to this repository.

#### Docker Volume Configuration

- The wp-content folder in our repository is configured to map internally to our Wordpress container like so: `./wp-content:/var/www/html/wp-content`. Meaning that when you pull down the repository that the contents of the wp-content folder will be mounted by the container at `/var/www/html/wp-content`. This is set up in the `docker-compose.yml` file in the Wordpress container's volumes section.

#### Github WP Sync

- ! TODO - initially and periodically check .gitignore to ensure that it's properly scoped - i.e. wp-content directory - currently looks good, but we want to maybe keep an eye out for DB seeding / raking (also per Ryan a docker config / init script) as well as php settings (default docker / WP may not be ideal - usu in /etc/php7.-4 but I don't see that in the docker image)
- There is an 'export' and 'import' built into WP which handles the associated content (pages, posts, and media) - wp-admin/import.php and export.php -
- Currently the default user and password for both wordpress and mysql is exposed-- may want to use a secrets file.
  - This is also important for the init script in the [docker install](#docker-install) section (runs a command using the username and password in the shell)

#### Git submodule

We are currently using the `air-light` starter theme, which is forked into the Code for Chicago organization over [here](https://github.com/Code-For-Chicago/air-light). After you have cloned the ceic_wordpress repository locally you'll have to run two commands in order to pull in the theme dependency.

- `git submodule init`
- `git submodule update`

Once the second command is run you should see theme files under `wp-content/themes/air-light/`. In order to update your local repository with changes made to the submoduled theme you'll again need to run `git submodule update`.

#### Running in Production mode

We have a production yaml file named `docker-compose.prod.yml` that is used to define how the containers should operate within a hosted environment. This includes making use of an environment file to ensure that we aren't sharing things like credentials in our github repo. This can be started by using the following command instead of the typical `docker-compose up`: `docker-compose -f docker-compose.prod.yml up -d`. As you'll see, the main difference is the -f argument which indicates to docker-compose that it should use the prod file instead of the default.

- Note, you will also need a env file located at ./.env in order for this mode to function. Here is an example of what that looks like:

```
MYSQL_ROOT_PASSWORD=mysqlrootpassword
MYSQL_DATABASE=auser
MYSQL_USER=auser
MYSQL_PASSWORD=apassword
WORDPRESS_DB_HOST=db:1234
WORDPRESS_DB_USER=auser
WORDPRESS_DB_PASSWORD=apassword
WORDPRESS_DB_NAME=text
```

#### CSS Edits using Sass

Sass is installed and style updates in the child folder can be made to the `style.scss` file. For running and compiling locally, install [here](https://sass-lang.com/install).


This is some work Ryan has done.
