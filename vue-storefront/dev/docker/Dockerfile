FROM node:10

ENV NODE_CONFIG_ENV=development PM2_ARGS=--no-daemon BIND_HOST=0.0.0.0 VS_ENV=dev

WORKDIR /var/www

COPY . .

# Should be yarn install --production
RUN apt update && apt install -y git \
  && yarn install \
  && yarn build

# COPY dev/docker/vue-storefront.sh /usr/local/bin/
# RUN chmod a+x /usr/local/bin/vue-storefront.sh
#
# ENTRYPOINT ["vue-storefront.sh"]
