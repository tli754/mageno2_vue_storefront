FROM node:18-alpine
LABEL maintainer="Tao Li <tao.li@gmail.co.nz>"

RUN mkdir /project
WORKDIR /project

COPY ./package.json /project
COPY ./yarn.lock /project

RUN cd /project \
  && apk add --no-cache --virtual .build-deps \
  && apk add --no-cache ca-certificates \
  && apk add --no-cache wget \
  && apk add --no-cache python3 \
  && apk add --no-cache make \
  && apk add --no-cache g++ \
  && apk add --no-cache git \
  && apk add --no-cache curl \
  && yarn install --no-cache \
  && apk del .build-deps

ADD . /project
COPY ./entrypoint.sh /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]
