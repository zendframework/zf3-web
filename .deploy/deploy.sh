#!/bin/bash
function log_error_and_exit () {
    local LAST_COMMAND=$1
    local LAST_STATUS=$2
    logger "Error running $0; received ${LAST_STATUS} from ${LAST_COMMAND}"
    exit ${LAST_STATUS}
}

echo "[BEGIN] deploy framework.zend.com"
CHECKOUT_DIR=$(readlink -f $(dirname $0)/..)
echo "- Deployment directory: ${CHECKOUT_DIR}"

(
    cd $CHECKOUT_DIR ;

    # Copy in the production local configuration
    echo "- Syncing production configuration" ;
    aws s3 sync s3://zf-backup/config ${CHECKOUT_DIR}/config/autoload ;
    if [ $? != 0 ]; then log_error_and_exit aws_s3_sync $?; fi

    # Execute a composer installation
    echo "- Executing composer" ;
    COMPOSER_HOME=/var/cache/composer composer install --quiet --no-ansi --no-dev --no-interaction --no-progress --no-scripts --no-plugins --optimize-autoloader ;
    if [ $? != 0 ]; then log_error_and_exit composer_install $?; fi ;

    # Setting permissions for bin folder
    echo "- Setting permissions for bin scripts" ;
    chmod 750 bin/*.php ;

    # Build
    echo "- Building page files" ;
    php bin/build.php ;
    if [ $? != 0 ]; then log_error_and_exit build $?; fi ;

    # Generating statistics on the shared folder
    echo "- Generating statistics" ;
    php bin/stats.php /mnt/efs/stats/zendframework ;
    if [ $? != 0 ]; then log_error_and_exit stats $?; fi ;

    # Fetching and preparing LTS data
    echo "- Fetching and preparing LTS data" ;
    ./bin/zfweb.php lts:build ;
    if [ $? != 0 ]; then log_error_and_exit lts:build $?; fi ;

    # Install crontab
    echo "- Installing crontab" ;
    crontab < .deploy/www-data.crontab ;
    if [ $? != 0 ]; then log_error_and_exit crontab $?; fi
)

echo "[DONE] deploy framework.zend.com"
