FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Expose port
EXPOSE 80

CMD ["apache2-foreground"]
