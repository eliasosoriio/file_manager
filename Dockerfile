FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de configuración de Composer primero
COPY composer.json composer.lock ./

# Instalar dependencias de Composer (sin ejecutar scripts)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copiar el resto de archivos del proyecto
COPY . .

# Ahora ejecutar los scripts de Composer
RUN composer run-script post-install-cmd || true

# Crear directorios necesarios y establecer permisos
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 var

# Copiar configuración de Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copiar configuración de Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Exponer puerto
EXPOSE 8080

# Comando de inicio
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
