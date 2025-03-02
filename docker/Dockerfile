FROM php:8.4.4-fpm-alpine3.21

# Define build arguments
ARG USER_ID
ARG GROUP_ID

# Define environment variables
ENV USER_ID=$USER_ID
ENV GROUP_ID=$GROUP_ID
ENV USER_ID=${USER_ID:-1000}
ENV GROUP_ID=${GROUP_ID:-1000}

# Add group and user based on build arguments
RUN addgroup --gid ${GROUP_ID} pkmed
RUN adduser --disabled-password --gecos '' --uid ${USER_ID} -G pkmed pkmed

# Set user and group of working directory
RUN chown -R pkmed:pkmed /var/www/html

RUN apk --no-cache update && apk --no-cache add bash && apk --no-cache add git
RUN apk add --update linux-headers
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS
RUN pecl install xdebug-3.4.0
RUN docker-php-ext-enable xdebug
RUN docker-php-ext-install pdo pdo_mysql
RUN apk del -f .build-deps

RUN echo "[Xdebug]" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.discover_client_host=true" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.ide_key=phpstorm" >> /usr/local/etc/php/conf.d/xdebug.ini

#install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
	&& php composer-setup.php \
	&& php -r "unlink('composer-setup.php');" \
	&& mv composer.phar /usr/local/bin/composer

#install cli symfony
RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html

RUN git config --global user.email "pkmed.dev@gmail.com"
RUN git config --global user.name "pkmed"

USER pkmed