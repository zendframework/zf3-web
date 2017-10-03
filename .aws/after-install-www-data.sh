#!/bin/bash
#######################################################################
# Application preparation
#######################################################################

(
    cd /var/vhosts/framework.zend.com/htdocs ;

    # Copy in the production local configuration
    echo "Syncing production configuration" ;
    aws s3 sync s3://zf-backup/config /var/vhosts/framework.zend.com/htdocs/config/autoload ;

    # Execute a composer installation (with dev dependencies)
    echo "Executing composer" ;
    COMPOSER_HOME=/var/cache/composer composer install --quiet --no-ansi --no-interaction --no-progress --no-scripts --no-plugins --optimize-autoloader ;

    # Setting permissions for bin folder
    echo "Setting permissions" ;
    chmod 750 bin/*.php ;

    # Build
    echo "Building page files" ;
    php bin/build.php ;

    # Execute a composer installation (without dev dependencies)
    echo "Executing composer" ;
    COMPOSER_HOME=/var/cache/composer composer install --quiet --no-ansi --no-dev --no-interaction --no-progress --no-scripts --no-plugins --optimize-autoloader ;

    # Generating statistics on the shared folder
    echo "Generating statistics" ;
    php bin/stats.php /mnt/efs/stats/zendframework;
)

echo "[DONE] after-install-www-data.sh"
exit 0
