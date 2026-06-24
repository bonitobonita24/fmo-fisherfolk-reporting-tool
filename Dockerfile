# FMO Fisherfolk Reporting Tool — PHP 8.3 + Apache + SQLite
# pdo_sqlite / sqlite3 are already enabled in the official php:8.3-apache image.
FROM php:8.3-apache

# Align the web-server user with the host user so bind-mounted data/ and
# uploads/ stay writable on Docker Desktop (WSL2 / Linux). Override at build
# time with --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) if needed.
ARG PUID=1000
ARG PGID=1000

# Apache modules used by the app's .htaccess
RUN a2enmod rewrite deflate expires headers

# Production-style PHP settings
RUN { \
        echo 'display_errors = Off'; \
        echo 'log_errors = On'; \
        echo 'expose_php = Off'; \
        echo 'upload_max_filesize = 16M'; \
        echo 'post_max_size = 20M'; \
    } > /usr/local/etc/php/conf.d/zz-app.ini

# Document root -> public/, with our vhost
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Re-map www-data to the host UID/GID (only if different from default)
RUN if [ "$PGID" != "33" ]; then groupmod -o -g "$PGID" www-data; fi \
 && if [ "$PUID" != "33" ]; then usermod  -o -u "$PUID" www-data; fi

WORKDIR /var/www/html

# App code (data/ and public/uploads are excluded via .dockerignore and
# provided at runtime through bind mounts so PII never lands in image layers).
COPY --chown=www-data:www-data . /var/www/html

# Ensure runtime-writable dirs exist and are owned by the web user
RUN mkdir -p data public/uploads \
 && chown -R www-data:www-data data public/uploads

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD php -r '$c=@file_get_contents("http://localhost/api/summary-stats.php"); exit(strpos($c,"success")!==false?0:1);' || exit 1

EXPOSE 80
