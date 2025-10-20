FROM php:8.3-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite (optional, useful for clean URLs)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html