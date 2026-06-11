FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev libzip-dev zip nodejs npm \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql zip

    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

RUN php artisan config:clear

EXPOSE 10000

CMD php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan storage:link && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
