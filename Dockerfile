FROM php:8.2-apache
RUN docker-php-ext-install mysqli
COPY . /var/www/html/

# Ensure www-data (the user Apache runs as) can write to upload/output folders
RUN mkdir -p /var/www/html/uploads /var/www/html/qr_codes /var/www/html/uploadprofile \
    && chown -R www-data:www-data /var/www/html/uploads /var/www/html/qr_codes /var/www/html/uploadprofile \
    && chmod -R 755 /var/www/html/uploads /var/www/html/qr_codes /var/www/html/uploadprofile

EXPOSE 80