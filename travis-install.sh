#!/usr/bin/env bash
set -ev


#Clean mysql database
mysql -e "DROP DATABASE IF EXISTS magento_test; CREATE DATABASE IF NOT EXISTS magento_test;" -uroot

cd $TRAVIS_BUILD_DIR/build

# Install Magento
n98-magerun.phar install --magentoVersion ${MAGENTO_VERSION} --installationFolder "magento" --dbHost "127.0.0.1" --dbUser "root" --dbPass "" --dbName "magento_test" --baseUrl "http://testmagento.local" --forceUseDb --useDefaultConfigParams yes --installSampleData no
mkdir -p magento/var/log

# Install our module
cd $TRAVIS_BUILD_DIR/build/magento
n98-magerun.phar sys:info

modman init
modman link $TRAVIS_BUILD_DIR



if [ $NO_DEPS ]
then
    #only install test dependencies
    composer require phpunit/phpunit
    composer require satooshi/php-coveralls
else
    cp $TRAVIS_BUILD_DIR/composer.json .

    #add magento-root-dir directive
    sed -i 's/"require":/"extra": {"magento-root-dir": "magento\/"},\n    "require":/' composer.json

    composer install
fi

if [ $TRAVIS_PHP_VERSION == "7.0" ]
then
    #make php7 possible
    composer config repositories.inchoo vcs https://github.com/Inchoo/Inchoo_PHP7

    if [ $MAGENTO_VERSION == "MAGENTO_VERSION=magento-mirror-1.9.3.0" ]
    then
        composer require inchoo/php7 2.0.0
    else
        composer require inchoo/php7 1.0.6
    fi
fi

