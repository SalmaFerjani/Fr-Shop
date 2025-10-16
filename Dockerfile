# =============================================================================
# Dockerfile pour la production - La Boutique Française
# =============================================================================

FROM php:8.1-fpm-alpine

# Installation des dépendances système pour MySQL
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    mysql-client \
    mysql-dev \
    icu-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    nginx

# Installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    xml \
    mysqli

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration du répertoire de travail
WORKDIR /app

# Copie du code source
COPY . .

# Installation des dépendances (production)
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

# Configuration de l'environnement de production
ENV APP_ENV=prod
ENV APP_DEBUG=0

    # Création des dossiers nécessaires et configuration des permissions
    RUN mkdir -p /app/var /app/public/uploads \
        && chown -R www-data:www-data /app/var \
        && chmod -R 755 /app/public/uploads

# Configuration Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Exposé du port
EXPOSE 8000

# Script de démarrage
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Commande par défaut
CMD ["/start.sh"]
