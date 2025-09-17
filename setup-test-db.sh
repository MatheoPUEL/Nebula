#!/bin/bash

echo "Setting up test database..."

# Remove existing test database
rm -f /app/var/test.db

# Create test database
DATABASE_URL="sqlite:///$(pwd)/var/test.db" php bin/console doctrine:database:create --env=test

# Create schema
DATABASE_URL="sqlite:///$(pwd)/var/test.db" php bin/console doctrine:schema:create --env=test

echo "Test database setup complete!"
echo "Database location: /app/var/test.db"
