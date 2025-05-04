FROM php:8.2-apache

# install ekstensi yang dibutuhkan
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev zip unzip git curl \
 && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
 && a2enmod rewrite

# enable & configure OPCache
RUN docker-php-ext-enable opcache \
  && { \
       echo "opcache.memory_consumption=128"; \
       echo "opcache.max_accelerated_files=10000"; \
       echo "opcache.revalidate_freq=0"; \
       echo "opcache.validate_timestamps=1"; \
     } > /usr/local/etc/php/conf.d/opcache.ini

# ubah DocumentRoot ke /var/www/html/public
RUN sed -ri \
      's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' \
      /etc/apache2/sites-available/000-default.conf \
  && sed -ri \
      's!<Directory /var/www/html>!<Directory /var/www/html/public>!g' \
      /etc/apache2/apache2.conf


# install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# install dependency
RUN composer install --no-interaction --optimize-autoloader

# atur permission
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]

