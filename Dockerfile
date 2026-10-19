FROM php:8.3-apache

# 1. Update package lists and install system requirements first
RUN apt-get update && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions needed for your app and Aiven MySQL
RUN docker-php-ext-install mysqli pdo_mysql mbstring curl

# 3. Enable URL rewriting for your .htaccess file
RUN a2enmod rewrite

# 4. Set the active working directory
WORKDIR /var/www/html
