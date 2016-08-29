#!/bin/bash
#######################################################################
# System dependencies
#######################################################################

# Get Composer, and install to /usr/local/bin
if [ ! -f "/usr/local/bin/composer" ];then
    echo "Installing composer"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
else
    echo "Updating composer"
    /usr/local/bin/composer self-update -q --stable --no-ansi --no-interaction
fi

# Create a COMPOSER_HOME directory for the application
if [ ! -d "/var/cache/composer" ];then
    echo "Creating composer cache directory"
    mkdir -p /var/cache/composer
    chown www-data.www-data /var/cache/composer
fi

# Create the cache folder for the application
if [ ! -d "/mnt/local/cache" ];then
    echo "Creating the local cache"
    mkdir /mnt/local/cache
    chown www-data.www-data /mnt/local/cache -R
fi

# Create the shared stats folder
if [ ! -d "/mnt/efs/stats" ];then
    echo "Creating the shared stats folder"
    mkdir -p /mnt/efs/stats
    chown www-data.www-data /mnt/efs/stats
fi

echo "[DONE] before-install.sh"
exit 0
