
FROM php:8.4-apache
 
RUN docker-php-ext-install pdo pdo_mysql mysqli
 
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork rewrite
 
COPY . /var/www/html/
 
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
 
EXPOSE 80
 
CMD ["apache2-foreground"]
 
