FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar todo el proyecto
COPY . /var/www/html/

# Configurar Apache para permitir .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Cambiar DocumentRoot a /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Asegurar que /api siga siendo accesible
RUN sed -i '/DocumentRoot/a <Directory /var/www/html/api>\n    AllowOverride All\n    Require all granted\n</Directory>' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]


