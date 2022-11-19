FROM php:8.0-cli-alpine3.13

ENV ACCEPT_EULA=Y

# Install prerequisites required for tools and extensions installed later on.
RUN apk add --update bash gnupg less libpng-dev libzip-dev nano nodejs npm su-exec unzip

# Install yarn as global npm package.
RUN npm install -g yarn

# Install prerequisites for the sqlsrv and pdo_sqlsrv PHP extensions.
RUN curl -O https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.7.1.1-1_amd64.apk \
    && curl -O https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/mssql-tools_17.7.1.1-1_amd64.apk \
    && curl -O https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/msodbcsql17_17.7.1.1-1_amd64.sig \
    && curl -O https://download.microsoft.com/download/e/4/e/e4e67866-dffd-428c-aac7-8d28ddafb39b/mssql-tools_17.7.1.1-1_amd64.sig \
    && curl https://packages.microsoft.com/keys/microsoft.asc  | gpg --import - \
    && gpg --verify msodbcsql17_17.7.1.1-1_amd64.sig msodbcsql17_17.7.1.1-1_amd64.apk \
    && gpg --verify mssql-tools_17.7.1.1-1_amd64.sig mssql-tools_17.7.1.1-1_amd64.apk \
    && apk add --allow-untrusted msodbcsql17_17.7.1.1-1_amd64.apk mssql-tools_17.7.1.1-1_amd64.apk \
    && rm *.apk *.sig

# Retrieve the script used to install PHP extensions from the source container.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/install-php-extensions

# Install required PHP extensions and all their prerequisites available via apt.
RUN chmod uga+x /usr/bin/install-php-extensions \
    && sync \
    && install-php-extensions bcmath ds exif gd intl opcache pcntl pcov pdo_sqlsrv redis sqlsrv zip swoole

# Downloading composer and marking it as executable.
RUN curl -o /usr/local/bin/composer https://getcomposer.org/composer-stable.phar \
    && chmod +x /usr/local/bin/composer

##
# ---------- env settings ----------
##
# --build-arg timezone=Asia/Shanghai
ARG timezone

ENV TIMEZONE=${timezone:-"Asia/Shanghai"} \
    APP_ENV=prod \
    SCAN_CACHEABLE=(true) \
    COMPOSER_ALLOW_SUPERUSER=1

# update
RUN set -ex \
    # show php version and extensions
    && php -v \
    && php -m \
    && php --ri swoole \
    && php --ini \
    #  ---------- some config ----------
    && cd /usr/local/etc/php/ \
    && touch conf.d/99_overrides.ini \
    # - config PHP
    && { \
        echo "upload_max_filesize=128M"; \
        echo "post_max_size=128M"; \
        echo "memory_limit=1G"; \
        echo "date.timezone=${TIMEZONE}"; \
        echo "swoole.use_shortname='Off'"; \
    } | tee conf.d/99_overrides.ini \
    # - config timezone
    && ln -sf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "${TIMEZONE}" > /etc/timezone \
    # ---------- clear works ----------
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"

# Setting the work directory.
WORKDIR /opt/www

COPY . /opt/www
RUN composer install --no-dev -o && php bin/hyperf.php

EXPOSE 9501

ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "start"]
