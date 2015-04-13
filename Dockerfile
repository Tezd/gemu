FROM docker.sam-media.com/hhvm


ENV SAM_ENV production

COPY . /var/www/html/gemu
WORKDIR /var/www/html/gemu
