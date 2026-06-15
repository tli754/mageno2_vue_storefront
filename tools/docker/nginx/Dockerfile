FROM alpine:3.8
MAINTAINER Tao <tao.li.7019@gmail.com>

# Create app user with app group. Specifically assign uid and gid 1000
# to app user and group so that it matches the host user and group id
# on some major host operating systems.
RUN mkdir /app && \
    /usr/sbin/addgroup -g 1000 app && \
    /usr/sbin/adduser -D -H -h /app -u 1000 -G app app && \
    /bin/chown -R app:app /app

# Add dependencies.
RUN apk --no-cache add \
        bash \
        libuuid \
        apr \
        apr-util \
        libjpeg-turbo \
        icu \
        icu-libs \
        pcre \
        zlib \
        libressl libressl-dev \
        nginx \
        supervisor \
        curl

RUN mkdir -p /run/nginx
#ADD ./conf.d /etc/nginx/conf.d
VOLUME ["/var/log/nginx"]

EXPOSE 80 443

# Set nginx directly as the entrypoint.
ENTRYPOINT ["nginx", "-g", "daemon off;"]
