#!/bin/bash

echo "Cleaning up test database..."

# Remove test database file
if [ -f "/app/var/test.db" ]; then
    rm -f /app/var/test.db
    echo "Test database removed: /app/var/test.db"
else
    echo "Test database file not found (already clean)"
fi

echo "Test database cleanup complete!"
