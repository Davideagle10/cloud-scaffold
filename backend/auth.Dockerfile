# Use an official PHP-FPM image as a parent image
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    locales \
    git \
    curl \
    zip \
    unzip \
    # Dependencias de pdo_mysql y mbstring
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    # Dependencias de GD y exif
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    # Paquetes extra que tenías (se recomienda limpiarlos)
    jpegoptim optipng pngquant gifsicle \
    vim \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql mbstring exif pcntl bcmath gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ========================================
# STAGE 1: Build and prepare application
# ========================================
# Create build directory for preparing the application
WORKDIR /build

# Step 1: Copy composer.json first for better Docker layer caching
COPY auth-service/composer.json ./

# Step 2: Install composer dependencies
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Step 3: Copy all auth-service application files
COPY auth-service/ ./

# Step 4: Copy shared Core and Middleware files from the common app directory
RUN mkdir -p app/Core app/Middleware
COPY app/Core/ ./app/Core/
COPY app/Middleware/ ./app/Middleware/

# Step 5: Generate optimized autoloader for production
RUN composer dump-autoload --optimize

# ========================================
# STAGE 2: Setup final working directory
# ========================================
# Set final working directory
WORKDIR /var/www

# Step 6: Copy the application files from build directory
RUN cp -r /build/. /var/www/ && \
    rm -rf /build

# Step 7: Copy Core and Middleware files directly to final location
RUN mkdir -p /var/www/app/Core /var/www/app/Middleware
COPY app/Core/ /var/www/app/Core/
COPY app/Middleware/ /var/www/app/Middleware/ 

# Note: Environment variables will be provided by docker-compose.yml
# Change permissions of the directory
RUN chown -R www-data:www-data /var/www

# Copy custom PHP-FPM configuration
COPY php-fpm-fixed.conf /usr/local/etc/php-fpm.d/www.conf

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
