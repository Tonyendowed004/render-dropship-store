FROM php:8.3-apache
RUN docker-php-ext-install pdo_mysql mbstring && apt-get update && apt-get install -y --no-install-recommends libcurl4-openssl-dev && docker-php-ext-install curl && rm -rf /var/lib/apt/lists/*
RUN a2enmod rewrite
WORKDIR /var/www/html
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html
EXPOSE 80
