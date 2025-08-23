FROM php:8.4-alpine
RUN docker-php-ext-install mysqli

COPY src/config.example.php src/config.php
