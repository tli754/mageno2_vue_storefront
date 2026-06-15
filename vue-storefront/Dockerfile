FROM node:18-alpine
LABEL maintainer="Tao Li <tao.li.7019@gmail.com>"

RUN mkdir /project

COPY ./package.json /project
COPY ./yarn.lock /project

RUN cd /project \
  && apk add --no-cache --virtual .build-deps ca-certificates wget python3 make g++ \
  && apk add --no-cache git \
  && apk add --no-cache curl \
  && yarn install --no-cache \
  && apk del .build-deps

ADD . /project
WORKDIR /project

COPY ./entrypoint.sh /entrypoint.sh

EXPOSE 3000
ENTRYPOINT ["/entrypoint.sh"]
