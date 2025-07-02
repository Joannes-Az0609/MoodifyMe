# Use official PHP image with Apache
FROM php:8.1-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Create necessary directories and set permissions
RUN mkdir -p uploads logs cache \
    && chmod 755 uploads logs cache \
    && chown -R www-data:www-data uploads logs cache

# Expose port
EXPOSE $PORT

# Start PHP built-in server
CMD php -S 0.0.0.0:$PORT -t .
