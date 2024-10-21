FROM php:8.1.4-apache
# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    openssl \
    libssl-dev \
    procps \
    htop \
    vim \
    wget

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets mysqli pdo_mysql

# Copia configuracao do apache
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY . .

# Instala o composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

EXPOSE 80

CMD ["apache2-foreground"]
