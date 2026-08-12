FROM php:7.4-apache

# Dependências de sistema e extensões PHP exigidas pelo CodeIgniter 3 / Ion Auth.
# `opcache` é a extensão mais importante aqui: a imagem base php:7.4-apache
# NÃO vem com ela habilitada por padrão (ver docker/php/opcache.ini para
# detalhes) — sem isso todo request recompila o PHP do zero.
# `curl` é usado pelo healthcheck do próprio container (docker-compose.yml).
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        zip \
        unzip \
        git \
        curl \
    && docker-php-ext-install mysqli pdo pdo_mysql zip opcache \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Permite uso de .htaccess (mod_rewrite) dentro do vhost padrão
RUN sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Configurações de performance de PHP e Apache (ver docker/php e docker/apache
# para o racional de cada ajuste).
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/php/local.ini /usr/local/etc/php/conf.d/zz-local.ini
COPY docker/apache/perf.conf /etc/apache2/conf-enabled/zz-perf.conf

COPY docker/scripts/start-app.sh /usr/local/bin/start-app.sh
RUN chmod +x /usr/local/bin/start-app.sh

WORKDIR /var/www/html

COPY . /var/www/html/

# Diretórios que o CodeIgniter precisa escrever (cache/logs/sessões em arquivo)
RUN mkdir -p application/cache application/logs /var/lib/ci-sessions \
    && chown -R www-data:www-data /var/www/html /var/lib/ci-sessions \
    && chmod -R 775 application/cache application/logs /var/lib/ci-sessions

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/start-app.sh"]
