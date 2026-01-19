FROM dunglas/frankenphp:1.10.1-php8.4

RUN install-php-extensions \
	pdo_mysql \
	# redis \
	zip \
	opcache \
	intl \
	pcntl

ENV SERVER_NAME=:80