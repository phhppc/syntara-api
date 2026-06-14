FROM php:8.2-apache

# Instala extensões necessárias
RUN docker-php-ext-install pdo pdo_mysql

# Habilita mod_rewrite
RUN a2enmod rewrite

# Configura Apache para permitir .htaccess
RUN sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Copia todos os arquivos para o diretório do Apache
COPY . /var/www/html/

# Permissões corretas
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
