FROM php:8.2-fpm-bullseye as base

ARG project_root=.

# install Composer
COPY ${project_root}/docker/docker-install-composer /usr/local/bin/docker-install-composer

RUN chmod +x /usr/local/bin/docker-install-composer && \
    docker-install-composer

# set www-data user his home directory
# the user "www-data" is used when running the image, and therefore should own the workdir
RUN usermod -m -d /home/www-data www-data && \
    mkdir -p /var/www/html && \
    chown -R www-data:www-data /home/www-data /var/www/html

# Switch to user
USER www-data

COPY ./ /var/www/html

RUN composer install --optimize-autoloader --no-interaction --no-progress --no-dev

