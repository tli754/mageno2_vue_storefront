#!/usr/bin/env sh

CMD_MAGENTO="/magento2/bin/magento" && chmod +x ${CMD_MAGENTO}

case "$1" in
    "")
        if [ "$APPLICATION_ENV" = "production" ]
        then
            # PRODUCTION
            echo "Switching to production mode"
            # Calculate the number of seconds required to bootstrap the container
            # Measure the time it takes to bootstrap the container
            START=`date +%s`
            cd /magento2
            rm -rf var/cache/* var/di/* var/generation/* var/page_cache/* var/view_preprocessed/* pub/static/backend/*
            $CMD_MAGENTO setup:upgrade
            $CMD_MAGENTO setup:di:compile
            $CMD_MAGENTO setup:static-content:deploy --theme Magento/backend --area adminhtml
            $CMD_MAGENTO indexer:reindex
            $CMD_MAGENTO cache:clean
            $CMD_MAGENTO cache:flush
            $CMD_MAGENTO deploy:mode:set production -s
            chmod -R 777 var/ pub/static/ pub/media/ generated/ app/etc/*
            END=`date +%s`
            RUNTIME=$((END-START))
            echo "Startup preparation finished in ${RUNTIME} seconds"
        fi
        if [ "$APPLICATION_ENV" = "development" ]
        then
            #  DEVELOPMENT
            echo "Switching to development mode"
            $CMD_MAGENTO deploy:mode:set developer -s
        fi
        supervisord -c /etc/supervisor/supervisor.conf
        ;;
    "fpm")
        /usr/sbin/php-fpm7 -F -O 2>&1 | sed 's/WARNING: \[pool www\] child [0-9]* said into std[a-z]*: \"\(.*\)\"$/\1/'
        ;;
    "cron")
        crond -f -d 5 -c /crontab/
        ;;
    *)
        echo "Unsupported argument"
        exit 1
        ;;
esac

