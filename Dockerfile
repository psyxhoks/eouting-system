FROM php:8.2-apache

# Install system libraries required by the GD extension, then enable GD + mysqli
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

# Ensure www-data (the user Apache runs as) can write to upload/output folders
RUN mkdir -p /var/www/html/uploads /var/www/html/qr_codes /var/www/html/uploadprofile \
    && chown -R www-data:www-data /var/www/html/uploads /var/www/html/qr_codes /var/www/html/uploadprofile \
    && chmod -R 755 /var/www/html/uploads /var/www/html/qr_codes /var/www/html/uploadprofile

EXPOSE 80