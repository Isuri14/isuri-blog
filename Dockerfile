FROM php:8.2-cli-alpine

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Expose port 8000
EXPOSE 8000


# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]