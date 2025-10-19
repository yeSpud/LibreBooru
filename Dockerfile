FROM php:apache
RUN apt update && apt install -y curl git
COPY . /var/www/
RUN curl https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js -o /var/www/public_html/assets/classic/js/jquery.min.js && \
    git clone https://github.com/smarty-php/smarty /var/www/software/smarty && \
    git clone https://github.com/erusev/parsedown /var/www/software/parsedown && \
    sed -i 's/\/var\/www\/html/\/var\/www\/public_html/g' /etc/apache2/sites-enabled/000-default.conf && \
    a2enmod headers
