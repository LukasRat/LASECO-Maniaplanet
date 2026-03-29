FROM php:8.3-cli

LABEL org.opencontainers.image.source=https://github.com/LukasRat/LASECO-Maniaplanet
LABEL org.opencontainers.image.description="LASECO (Trackmania Server Controller)"

# Install required system packages
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxpm-dev \
    libzip-dev \
    libonig-dev \
    netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions required by LASECO/UASECO
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
        --with-xpm \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        mbstring \
        exif \
        ftp

# Check included extensions
RUN php -r "extension_loaded('simplexml') or die('simplexml missing');"
RUN php -r "extension_loaded('json')      or die('json missing');"
RUN php -r "extension_loaded('iconv')     or die('iconv missing');"

# Tune PHP for long-running daemon use
RUN { \
        echo "memory_limit = 256M"; \
        echo "max_execution_time = 0"; \
        echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT"; \
        echo "log_errors = On"; \
        echo "error_log = /app/uaseco/logs/php_errors.log"; \
        echo "allow_url_fopen = On"; \
    } > /usr/local/etc/php/conf.d/uaseco.ini

# Copy LASECO engine sources
COPY src/ /app/uaseco/

# Copy curated XML configurations and custom plugins
COPY config/ /app/uaseco/config/
COPY plugins/ /app/uaseco/plugins/

# Ensure log and cache directories exist and are writable
RUN mkdir -p /app/uaseco/logs /app/uaseco/cache \
    && chmod -R 777 /app/uaseco/logs /app/uaseco/cache

# Copy entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /app/uaseco

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
