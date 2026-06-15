FROM alpine:3.13
  #Labels
LABEL maintainer="Tao Li <tao.li.7019@gmail.com>" \
    decription="Magento 2" \
    version="2.3"

# Create app user with app group. Specifically assign uid and gid 1000
# to app user and group so that it matches the host user and group id
# on some major host operating systems.
RUN mkdir /app && \
    /usr/sbin/addgroup -g 1000 app && \
    /usr/sbin/adduser -D -H -h /app -u 1000 -G app app && \
    /bin/chown -R app:app /app

RUN echo "Package build version 0.1"

RUN echo "@community http://dl-cdn.alpinelinux.org/alpine/v3.13/community" >> /etc/apk/repositories && \
    echo "http://dl-cdn.alpinelinux.org/alpine/v3.13/main" >> /etc/apk/repositories && \
    apk --no-cache add \
    bash \
    rsync \
    ca-certificates \
    libuuid \
    apr \
    apr-util \
    libjpeg-turbo \
    icu \
    icu-libs \
    pcre \
    zlib \
    libressl libressl-dev \
    curl \
    supervisor \
    git \
    nginx \
    shadow \
    py-pip \
    py-cffi \
    py-cryptography \
    py-crcmod \
    g++ \
    make \
    tzdata \
    libffi-dev \
    nodejs \
    nodejs-npm

RUN cp /usr/share/zoneinfo/Pacific/Auckland /etc/localtime

RUN apk --no-cache add \
    php7-fpm@community \
    php7-phar@community \
    php7-openssl@community \
    php7-curl@community \
    php7-json@community \
    php7-xml@community \
    php7-mcrypt@community \
    php7-zlib@community \
    php7-ctype@community \
    php7-iconv@community \
    php7-session@community \
    php7-dom@community \
    php7-bcmath@community \
    php7-mbstring@community \
    php7-pcntl@community \
    php7-gd@community \
    php7-xdebug@community \
    php7-opcache@community \
    php7-tokenizer php7-xmlwriter php7-simplexml \
    php7-sockets \
    php7-amqp \
    php7-fileinfo \
    php7-intl \
    mysql mysql-client php7-pdo_mysql@community \
    php7-dev \
    php7-soap \
    php7-xsl \
    php7-zip \
    php7-pear

RUN pecl install redis && \
echo "extension=redis.so" > /etc/php7/conf.d/20_redis.ini

#
# PHP Configuration
#
ENV PHP_INI=/etc/php7/php.ini
RUN \
    sed 's,;always_populate_raw_post_data,always_populate_raw_post_data,g' -i $PHP_INI && \
    sed 's,memory_limit = 128M,memory_limit = 2048M,g' -i $PHP_INI && \
    sed 's,upload_max_filesize = 2M,upload_max_filesize = 20M,g' -i $PHP_INI

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer --version=1.10.17
# install prestissimo globally as a separate (cached) layer. This speeds up composer, but of course takes time to install.
# In this case, we should probably have an intermediate container here - tradetested/magento:base
RUN composer global require hirak/prestissimo

ADD ./magento2 /magento2

# Set working directory
WORKDIR /

# Install dependencies with Composer.
# --prefer-source fixes issues with download limits on Github.
# --no-interaction makes sure composer can run fully automated
RUN cd /magento2 && composer install --prefer-source --no-interaction

# Copy existing application directory contents
COPY ./tools/supervisor.conf /etc/supervisor/supervisor.conf
COPY ./tools/start.sh /start.sh

COPY ./tools/config/magento/env.php /magento2/app/etc/env.php
ADD ./tools/config/php/www.conf /etc/php7/php-fpm.d/www.conf
ADD ./tools/config/nginx/conf.d /etc/nginx/conf.d
ADD ./tools/config/nginx/includes /etc/nginx/includes

RUN mkdir /run/nginx \
  && usermod -aG www-data nginx

VOLUME ["/var/log/nginx"]

EXPOSE 8080 8081 8082 8083
ENTRYPOINT ["/start.sh"]
HEALTHCHECK --interval=2m --start-period=30s --timeout=3s CMD curl --fail -vvv http://localhost:8081 || exit 1
