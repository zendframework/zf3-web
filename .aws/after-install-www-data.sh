#!/bin/bash
#######################################################################
# Application preparation
#######################################################################

(
    cd /var/vhosts/framework.zend.com/htdocs ;

    # Copy in the production local configuration
    echo "Syncing production configuration" ;
    aws s3 sync s3://zf-backup/config /var/vhosts/framework.zend.com/htdocs/config/autoload ;

    # Execute a composer installation
    echo "Executing composer" ;
    COMPOSER_HOME=/var/cache/composer composer install --quiet --no-ansi --no-dev --no-interaction --no-progress --no-scripts --no-plugins --optimize-autoloader ;

    # Setting permissions for bin folder
    echo "Setting permissions" ;
    chmod 750 bin/*.php ;

    # Build
    echo "Building page files" ;
    php bin/build.php ;

    # Generating statistics on the shared folder
    echo "Generating statistics" ;
    php bin/stats.php /mnt/efs/stats/zendframework ;

    # Fetching and preparing LTS data
    echo "Fetching and preparing LTS data" ;
    ./bin/zfweb.php lts:build ;

    # Install crontab
    echo "Installing crontab" ;
    crontab < .aws/www-data.crontab ;
)

echo "[DONE] after-install-www-data.sh"
exit 0
