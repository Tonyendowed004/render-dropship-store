FROM php:8.3-apache

# 1. Update package lists and install system requirements
RUN apt-get update && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions needed for your app and Aiven MySQL
RUN docker-php-ext-install mysqli pdo_mysql mbstring curl

# 3. Enable URL rewriting for your .htaccess file
RUN a2enmod rewrite

# 4. Copy your project files from the subfolder into Apache's public directory
COPY ./unirw-cj-dropshipping-store/ /var/www/html/

# 5. Set correct folder permissions for Apache
RUN chown -R www-data:www-data /var/www/html

# 6. Set the active working directory
WORKDIR /var/www/html
