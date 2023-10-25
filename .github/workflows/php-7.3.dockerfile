FROM php:7.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    iputils-ping \
    libldap2-dev \
    libpng-dev \
    libpq-dev \
    libsqlite3-dev \
    libzip-dev \
    mariadb-client \
    postgresql-client \
    sqlite3 \
    sudo \
    unzip \
    vim \
 && apt-get -y clean \
 && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install mbstring bcmath zip pdo pdo_mysql pdo_pgsql pdo_sqlite exif gd
COPY composer.json /composer.json
COPY composer.lock /composer.lock
COPY src /src
COPY test /test
RUN curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer; chmod +x /usr/local/bin/composer
RUN cd / && composer update
#RUN composer test
