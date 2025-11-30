#!/bin/sh
# Entrypoint: adjust Apache to listen on $PORT (Railway provides dynamic port)
: "${PORT:=8080}"
# Update ports.conf Listen directive
if [ -f /etc/apache2/ports.conf ]; then
  sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
fi
# Update virtual host files
for f in /etc/apache2/sites-available/*.conf; do
  [ -f "$f" ] || continue
  sed -ri "s/<VirtualHost \*:([0-9]+)>/<VirtualHost *:${PORT}>/g" "$f"
  sed -ri "s/VirtualHost \*:([0-9]+)/VirtualHost *:${PORT}/g" "$f"
done

export APACHE_RUN_USER=www-data
export APACHE_RUN_GROUP=www-data

exec apache2-foreground
