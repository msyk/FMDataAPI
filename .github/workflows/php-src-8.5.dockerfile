FROM --platform=linux/amd64 ubuntu:22.04
RUN export DEBIAN_FRONTEND=noninteractive \
 && apt update && apt install -y --no-install-recommends \
    software-properties-common \
    ca-certificates \
    wget \
    tar \
    git \
    pkg-config build-essential \
    libssl-dev \
    autoconf \
    gcc \
    make \
    curl \
    unzip \
    bison \
    re2c \
    locales \
    ldap-utils \
    openssl \
    slapd \
    language-pack-de \
    libgmp-dev \
    libicu-dev \
    libtidy-dev \
    libenchant-2-dev \
    libbz2-dev \
    libsasl2-dev \
    libxpm-dev \
    libzip-dev \
    libsqlite3-dev \
    libsqlite3-mod-spatialite \
    libwebp-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libxml2-dev \
    libxslt1-dev \
    libpq-dev \
    libreadline-dev \
    libldap2-dev \
    libsodium-dev \
    libargon2-0-dev \
    libmm-dev \
    libsnmp-dev \
    postgresql \
    postgresql-contrib \
    snmpd \
    snmp-mibs-downloader \
    freetds-dev \
    unixodbc-dev \
    llvm \
    clang \
    dovecot-core \
    dovecot-pop3d \
    dovecot-imapd \
    sendmail \
    firebird-dev \
    liblmdb-dev \
    libtokyocabinet-dev \
    libdb-dev \
    libqdbm-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
 && apt -y clean \
 && rm -rf /var/lib/apt/lists/*
RUN git clone --depth 1 --branch PHP-8.5 https://github.com/php/php-src.git
RUN cd php-src; export CC=clang; export CXX=clang++; export CFLAGS="-DZEND_TRACK_ARENA_ALLOC"; ./buildconf --force; ./configure --enable-debug --enable-mbstring --with-openssl --with-curl; make -j$(/usr/bin/nproc); make TEST_PHP_ARGS=-j$(/usr/bin/nproc) test; make install
COPY composer.json /composer.json
COPY composer.lock /composer.lock
COPY src /src
COPY test /test
RUN curl -sS https://getcomposer.org/installer | php; mv composer.phar /usr/local/bin/composer; chmod +x /usr/local/bin/composer
RUN cd / && composer update
#RUN composer test
CMD [ "/sbin/init" ]
