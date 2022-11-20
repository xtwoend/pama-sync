FROM xtwoend/mssql-swoole:1.0

ENV APP_ENV=prod \
    SCAN_CACHEABLE=(true) \
    COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /opt/www

COPY . /opt/www
RUN composer install --no-dev -o && php bin/hyperf.php

EXPOSE 9501

ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "start"]