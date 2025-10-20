FROM php:apache
RUN apt update && apt install -y curl git zlib1g-dev libpng-dev libjpeg-dev
COPY . /var/www/
RUN curl https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js -o /var/www/public_html/assets/classic/js/jquery.min.js && \
    git clone https://github.com/smarty-php/smarty /var/www/software/smarty && \
    git clone https://github.com/erusev/parsedown /var/www/software/parsedown && \
    sed -i 's/\/var\/www\/html/\/var\/www\/public_html/g' /etc/apache2/sites-enabled/000-default.conf && \
    a2enmod headers && \
    docker-php-ext-configure gd --enable-gd --with-jpeg && \
    docker-php-ext-install gd && \
    docker-php-ext-install mysqli && \

    # DO NOT DO THE FOLLOWING
    mkdir /var/www/__init/.tmp && chmod -R 777 /var/www/
