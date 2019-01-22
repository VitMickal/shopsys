#!/bin/bash
numberRegex='^[0-9]+([.][0-9]+)?$'
operatingSystem=''
allowedValues=(1 2)
declare -A operatingSystemsByIndex

operatingSystemsByIndex[1]='Linux'
operatingSystemsByIndex[2]='Mac'

echo This is installation script that will install demo Shopsys Framework application on docker with all required containers and with demo database created.

echo "Start with specifying your operating system: \
    1) Linux
    2) Mac
    "

while [[ 1 -eq 1 ]]
do
    read -p "Enter OS number: " operatingSystem
    if [[ ${operatingSystem} =~ $numberRegex ]] ; then
        if [[ " ${allowedValues[@]} " =~ " ${operatingSystem} " ]]; then
            break;
        fi

        echo "Not existing value, please enter one of existing values"
    else
        echo "Please enter a number"
    fi
done

echo "You chosed ${operatingSystemsByIndex[$operatingSystem]}. Now your app will be installed.."

echo "Creating config files.."
cp -f project-base/app/config/parameters.yml.dist project-base/app/config/parameters.yml
cp -f project-base/app/config/parameters_test.yml.dist project-base/app/config/parameters_test.yml
cp -f project-base/app/config/domains_urls.yml.dist project-base/app/config/domains_urls.yml


echo "Creating docker configurations.."
case "$operatingSystem" in
    "1")
        cp -f docker/conf/docker-compose.yml.dist docker-compose.yml
        ;;
    "2")
        cp -f docker/conf/docker-compose-mac.yml.dist docker-compose.yml
        cp -f docker/conf/docker-sync.yml.dist docker-sync.yml

        echo "You will be asked to enter sudo password in case to allow second domain alias in your system config.."
        sudo ifconfig lo0 alias 127.0.0.2 up

        mkdir -p project-base/var/postgres-data project-base/var/elasticsearch-data vendor
        docker-sync start
        ;;
esac

echo "Starting docker-compose.."

docker-compose up -d --build

echo "Installing application inside a php-fpm container"

docker-compose exec php-fpm composer install
docker-compose exec php-fpm ./phing build-demo-dev

echo "Your application is now ready under http://127.0.0.1:8000"
