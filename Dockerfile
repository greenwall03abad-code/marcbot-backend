FROM php:8.2-apache
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql curl \
    && a2dismod mpm_event mpm_worker \
    && a2enmod mpm_prefork rewrite headers
COPY . /var/www/html/
EXPOSE 80