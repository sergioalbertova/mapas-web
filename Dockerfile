FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar archivos al contenedor
COPY . /var/www/html/

# Configurar el directorio público
WORKDIR /var/www/html/public

# Configurar Apache para permitir .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Exponer el puerto
EXPOSE 80

CMD ["apache2-foreground"]
