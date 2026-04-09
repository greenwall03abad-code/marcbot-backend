FROM php:8.2-fpm-alpine
RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apk add --no-cache nginx curl
COPY . /var/www/html/
COPY nginx.conf /etc/nginx/nginx.conf
EXPOSE 80
CMD sh -c "php-fpm -D && nginx -g 'daemon off;'"