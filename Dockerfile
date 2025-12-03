FROM php:8.4-alpine
RUN docker-php-ext-install mysqli

COPY src src
COPY src/config.example.php src/config.php

CMD [ "php", "-S", "0.0.0.0:3000", "-t", "." ]
