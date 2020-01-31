# ceic_wordpress
Repository for work related to the Cannabis Equity Illinois Coalition Website

## Technology Stack
Docker Wordpress container - will pull latest based on YAML file.

### Notes

#### Docker Install
* install docker, sudo dockerd -H 127.0.0.1:2375, then export DOCKER_HOST=127.0.0.1:2375, then then 'docker-compose.yml' file and docker-compose up, then docker exec -it docker_wordpress_1 bash

#### Github WP Sync
* ! TODO - initially and periodically check .gitignore to ensure that it's properly scoped - i.e. wp-content directory - currently looks good, but we want to maybe keep an eye out for DB seeding / raking (also per Ryan a docker config / init script) as well as php settings (default docker / WP may not be ideal - usu in /etc/php7.-4 but I don't see that in the docker image)
* There is an 'export' and 'import' built into WP which handles the associated content (pages, posts, and media) - wp-admin/import.php and export.php - 
