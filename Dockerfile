FROM php:8.2-cli
RUN docker-php-ext-install mysqli pdo pdo_mysql
COPY . /app
WORKDIR /app
EXPOSE 80
CMD ["php", "-d", "max_execution_time=120", "-S", "0.0.0.0:80", "-t", "/app"]