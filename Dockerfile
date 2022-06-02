FROM php:7.4-cli
COPY . /usr/src/uf_ccoi_live
WORKDIR /usr/src/uf_ccoi_live
CMD [ "php", "./index.php" ]