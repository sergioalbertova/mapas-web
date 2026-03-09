FROM php:8.2-apache

# Dependencias necesarias para Dompdf
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    fonts-dejavu-core \
    && docker-php-ext-install pdo pdo_pgsql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar Composer dentro del contenedor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar proyecto completo
COPY . /var/www/html/

# Instalar dependencias PHP (incluye dompdf)
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

# Configurar Apache
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

RUN printf "<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
\n\
</VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
