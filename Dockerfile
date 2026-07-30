FROM php:8.2-apache

RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY . /var/www/html/

RUN mkdir -p /var/www/html/public/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod 777 /var/www/html/public/uploads

EXPOSE 80
