FROM php:8.2-cli
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql curl
COPY . /app
WORKDIR /app
EXPOSE 80
CMD ["php", "-d", "max_execution_time=120", "-S", "0.0.0.0:80", "-t", "/app"]