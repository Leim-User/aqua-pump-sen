FROM php:8.2-apache

# Install mysqli
RUN docker-php-ext-install mysqli

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project into Apache web directory
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/

# Apache listens on port 80
EXPOSE 80