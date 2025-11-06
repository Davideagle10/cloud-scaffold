# Stage 1: Install dependencies with Composer
FROM composer:2 as vendor
WORKDIR /app
COPY backend/composer.json .
#COPY backend/composer.lock .
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Stage 2: Build the final PHP-FPM image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install required PHP extensions
RUN docker-php-ext-install pdo_mysql && docker-php-ext-enable opcache

# Copy application code from the host
COPY backend/ /var/www/

# Copy the vendor directory from the composer stage
COPY --from=vendor /app/vendor/ /var/www/vendor/

# Set correct permissions for the application files
# The www-data user is the default for PHP-FPM
RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www

# Expose the port on which PHP-FPM listens
EXPOSE 9000

# The default command for the php-fpm image is to start the FPM server
CMD ["php-fpm"]