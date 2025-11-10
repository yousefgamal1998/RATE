# استخدم نسخة PHP مناسبة مع Apache
FROM php:8.2-apache

# ثبّت الإضافات المطلوبة للـ Laravel
RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl \
    && docker-php-ext-install pdo_mysql zip exif pcntl bcmath gd

# تفعيل mod_rewrite في Apache
RUN a2enmod rewrite

# إعداد مجلد التطبيق
WORKDIR /var/www/html

# نسخ ملفات المشروع
COPY . .

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت مكتبات Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# إعداد صلاحيات الملفات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# إنشاء key للتطبيق (لو لسه مش متولدة)
ENV APP_ENV=production
RUN php artisan key:generate || true

# تحديد المنفذ اللي Apache هيشتغل عليه
EXPOSE 80

# تشغيل Apache
CMD ["apache2-foreground"]
