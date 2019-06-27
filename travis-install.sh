#!/usr/bin/env bash
set -ev


#Clean mysql database
mysql -e "DROP DATABASE IF EXISTS magento_test; CREATE DATABASE IF NOT EXISTS magento_test;" -uroot

cd $TRAVIS_BUILD_DIR/build

#1.9.3.0 is currently not in the default version of n98 so add it manually
cat <<EOF > ~/.n98-magerun.yaml
commands:
  N98\Magento\Command\Installer\InstallCommand:
    magento-packages:
      - name: magento-mirror-1.9.3.10
        version: 1.9.3.10
        dist:
          url: https://github.com/OpenMage/magento-mirror/archive/1.9.3.10.zip
          type: zip
        extra:
          sample-data: sample-data-1.9.1.0
      - name: magento-mirror-1.9.4.1
        version: 1.9.4.1
        dist:
          url: https://github.com/OpenMage/magento-mirror/archive/1.9.4.1.zip
          type: zip
        extra:
          sample-data: sample-data-1.9.1.0
      #1.7.0.2  overwrite version due to https://github.com/ecoco/magento_profiler/issues/19
      - name: ecocode-mirror-1.7.0.2
        version: 1.7.0.2
        dist:
          url: https://github.com/ecoco/magento-mirror/archive/1.7.0.2-fix.zip
          type: zip
        extra:
          sample-data: sample-data-1.6.1.0
EOF


# Install Magento
n98-magerun.phar install --magentoVersion ${MAGENTO_VERSION} --installationFolder "magento" --dbHost "127.0.0.1" --dbUser "root" --dbPass "" --dbName "magento_test" --baseUrl "http://testmagento.local" --forceUseDb --useDefaultConfigParams yes --installSampleData no
mkdir -p magento/var/log

mkdir $TRAVIS_BUILD_DIR/util
cd $TRAVIS_BUILD_DIR/util
#only install test dependencies
composer require satooshi/php-coveralls --no-interaction

#DO NOT USE COMPOSER FOR PHPUNIT as it shares its autoloader
if [ $TRAVIS_PHP_VERSION == "5.5" ]
then
    wget https://phar.phpunit.de/phpunit-4.8.31.phar
    mv phpunit-4.8.31.phar phpunit.phar
else
    wget https://phar.phpunit.de/phpunit-5.7.phar
    mv phpunit-5.7.phar phpunit.phar
fi
chmod +x phpunit.phar

# Install our module
cd $TRAVIS_BUILD_DIR/build/magento
n98-magerun.phar sys:info

modman init
modman link $TRAVIS_BUILD_DIR


if [ $NO_DEPS ]
then
    #create minimal composer json to make sure we can install inchoo php7
    echo 'Install with no deps';
    echo '{"name": "ecocode/magento_profiler", "extra": {"magento-root-dir": "magento/"}}' > ./composer.json
else
    echo 'Install composer deps';
    cp $TRAVIS_BUILD_DIR/composer.json .

    #add magento-root-dir directive
    sed -i 's/"require":/"extra": {"magento-root-dir": "magento\/"},\n    "require":/' composer.json

    composer install --no-dev --no-interaction
fi

if [ $TRAVIS_PHP_VERSION == "7.1" ]
then
    #make php7 possible

    if [ $MAGENTO_VERSION == "magento-mirror-1.9.3.10" ] || [ $MAGENTO_VERSION == "magento-mirror-1.9.4.1"  ]
    then
        # do nothing no longer needed
        echo 'magento version is new enough no php 7 patch required'
    else
        composer config -g repositories.firegento composer https://packages.firegento.com --no-interaction
        composer require inchoo/php7 1.1.0 --no-interaction
    fi
fi

