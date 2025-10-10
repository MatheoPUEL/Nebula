#!/bin/sh
set -e

# Seuls les scripts appelant php ou bin/console doivent déclencher l'init
if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then

    # Installation du projet la première fois
    if [ ! -f composer.json ]; then
        echo "Installing Symfony project..."
        rm -Rf tmp/
        composer create-project "symfony/skeleton $SYMFONY_VERSION" tmp --stability="$STABILITY" --prefer-dist --no-progress --no-interaction --no-install

        cd tmp
        cp -Rp . ..
        cd -
        rm -Rf tmp/

        composer require "php:>=$PHP_VERSION" runtime/frankenphp-symfony
        composer config --json extra.symfony.docker 'true'

        if grep -q ^DATABASE_URL= .env; then
            echo 'To finish the installation, press Ctrl+C and run: docker compose up --build --wait'
            sleep infinity
        fi
    fi

    # Installation des dépendances si non présentes
    if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
        echo "Installing PHP dependencies..."
        composer install --prefer-dist --no-progress --no-interaction
    fi

    php bin/console -V

    # Vérification et attente de la DB
    if grep -q ^DATABASE_URL= .env; then
        echo 'Waiting for database to be ready...'
        ATTEMPTS_LEFT=60
        until [ $ATTEMPTS_LEFT -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
            if [ $? -eq 255 ]; then
                ATTEMPTS_LEFT=0
                break
            fi
            sleep 1
            ATTEMPTS_LEFT=$((ATTEMPTS_LEFT - 1))
            echo "Still waiting for database... $ATTEMPTS_LEFT attempts left."
        done

        if [ $ATTEMPTS_LEFT -eq 0 ]; then
            echo "Database is not reachable:"
            echo "$DATABASE_ERROR"
            exit 1
        fi
        echo 'Database ready!'

        # Migrations
        if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
            echo "Running migrations..."
            php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
        fi
    fi

    # Permissions
    setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
    setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

    echo 'PHP app ready!'
fi

# Exécuter le processus principal
exec docker-php-entrypoint "$@"
