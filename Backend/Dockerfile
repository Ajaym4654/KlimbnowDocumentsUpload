FROM php:7.4-fpm

# Install Nginx
RUN apt-get update && apt-get install -y nginx

# Copy custom Nginx config
COPY nginx.conf /etc/nginx/sites-available/default

# Set up the web root and copy application files
WORKDIR /var/www/html
COPY . .

# Expose port 80 for Nginx
EXPOSE 80

# Start Nginx and PHP-FPM
CMD service nginx start && php-fpm
