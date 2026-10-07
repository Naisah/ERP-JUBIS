#!/bin/bash
# Render injects a dynamic $PORT environment variable. Apache must listen on this port.
sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
php artisan route:cache
php artisan view:cache
apache2-foreground
