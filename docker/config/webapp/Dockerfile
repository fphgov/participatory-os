FROM php:8.1-fpm-alpine

# Locale
ENV LANG "C.UTF-8"
ENV LANGUAGE "C.UTF-8"
ENV LC_ALL "C.UTF-8"

RUN apk add --no-cache --update \
        linux-headers \
        curl-dev \
        git \
        wget \
        mysql-client \
        libzip-dev \
        supervisor \
        autoconf \
        make \
        automake \
        libtool \
        pkgconfig \
        bash \
        zlib-dev \
        icu-dev \
        g++ \
        libxml2-dev \
        patch

RUN docker-php-ext-configure intl && docker-php-ext-install intl

ENV LIBRARY_PATH=/lib:/usr/lib

ENV XDEBUG_MODE=off
ENV XDEBUG_CLIENT_HOST=host.docker.internal
ENV XDEBUG_CLIENT_PORT=9002
ENV XDEBUG_IDEKEY=VSCODE

WORKDIR /var/www/html

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === trim(file_get_contents('https://composer.github.io/installer.sig'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

RUN docker-php-ext-install mysqli pdo pdo_mysql zip opcache && \
    docker-php-ext-enable opcache

RUN apk add --no-cache cmake freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev && \
  docker-php-ext-configure gd && \
  NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
  docker-php-ext-install -j${NPROC} gd

# Install Xdebug
RUN apk add --no-cache $PHPIZE_DEPS && pecl install xdebug-3.1.5; \
    docker-php-ext-enable xdebug;

# Config Xdebug
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.mode=$XDEBUG_MODE" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.idekey=$XDEBUG_IDEKEY" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.discover_client_host=false" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.client_host=$XDEBUG_CLIENT_HOST" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.client_port=$XDEBUG_CLIENT_PORT" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.var_display_max_data=512" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.var_display_max_children=128" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    echo "xdebug.var_display_max_depth=3" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini;

HEALTHCHECK --interval=5m --timeout=3s CMD curl -f http://localhost/ || exit 1

COPY bash/crontab /etc/crontabs/docker-crontab

RUN tr -d "\r" < /etc/crontabs/docker-crontab

RUN chmod 0600 /etc/crontabs/docker-crontab

RUN /usr/bin/crontab /etc/crontabs/docker-crontab

EXPOSE $XDEBUG_CLIENT_PORT

RUN sed -i 's/9000/9004/' /usr/local/etc/php-fpm.d/zz-docker.conf

# https://spot13.com/pmcalculator/
RUN sed -i 's/pm.max_children = 5/pm.max_children = 816/' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/pm.start_servers = 2/pm.start_servers = 160/' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 80/' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 240/' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/;php_admin_value[memory_limit] = 32M/php_admin_value[memory_limit] = 4096M/' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/;pm.max_spawn_rate = 32/pm.max_spawn_rate = 40/' /usr/local/etc/php-fpm.d/www.conf
RUN sed -i 's/;pm.max_requests = 500/pm.max_requests = 480/' /usr/local/etc/php-fpm.d/www.conf

EXPOSE 9004

COPY supervisord.conf /etc/supervisord.conf

COPY docker/config/webapp/setup.sh /var/www/html/docker/config/webapp/setup.sh

RUN tr -d "\r" < /var/www/html/docker/config/webapp/setup.sh

RUN chmod +x /var/www/html/docker/config/webapp/setup.sh

RUN chmod 777 /var/www/html/public/files

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
