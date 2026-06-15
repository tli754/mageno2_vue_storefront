FROM alpine:latest
MAINTAINER Tao Li <tao.li.7019@gmail.com>

RUN echo "@community http://dl-cdn.alpinelinux.org/alpine/v3.9/community" >> /etc/apk/repositories && \
    apk add --update \
    alpine-sdk \
    nasm \
    git \
    python \
    nodejs \
    nodejs-npm \
    && \
    rm -rf /tmp/src && \
    rm -rf /var/cache/apk/*

RUN mkdir -p /gulp
WORKDIR /gulp
ADD ./package.json /gulp/package.json
RUN npm i && rm -rf /tmp/*

VOLUME /gulp/src
VOLUME /assets
EXPOSE 35729

ENTRYPOINT ["node", "/gulp/node_modules/.bin/gulp"]
