FROM php:7.4-cli

RUN apt-get update && apt-get upgrade -y \
    netcat \
    libonig-dev \
    git \
    libzip-dev \
    unzip \
    libmcrypt-dev \
    zlib1g-dev \
    libpng-dev \
    && docker-php-ext-install \
    iconv \
    mbstring \
    zip \
    gd \
    bcmath \
    mysqli \
    pdo \
    pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN /usr/bin/composer self-update --stable
RUN /usr/bin/composer --version
RUN /usr/bin/composer global require vimeo/psalm
COPY . /app
RUN groupadd -r table && useradd -m -g table table
RUN chown -R table /app
USER table
RUN sh /app/bin/build.sh