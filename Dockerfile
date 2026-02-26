FROM php:8.2-apache

RUN a2enmod rewrite

# Instalar drivers de PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

COPY . /var/www/html/

RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

RUN printf "<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
\n\
    <Directory /var/www/html/api>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
\n\
</VirtualHost>\n" > /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
