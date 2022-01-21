# docker/php-fpm/Dockerfile
FROM php:fpm-alpine
COPY waitfi.sh /usr/bin/waitfi
RUN chmod +x /usr/bin/waitfi
RUN apk --update --no-cache add git
RUN apk --update --no-cache add bash
RUN docker-php-ext-install pdo_mysql
COPY --from=composer /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
CMD composer install ; waitfi database:3306 -t 180 -- php bin/console doctrine:migrations:migrate ;  php-fpm 
EXPOSE 9000