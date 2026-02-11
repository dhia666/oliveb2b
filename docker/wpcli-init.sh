#!/bin/sh
set -eu

echo "Waiting for wp-config.php to exist..."
tries=60
until [ -f /var/www/html/wp-config.php ]; do
  tries=$((tries - 1))
  if [ "$tries" -le 0 ]; then
    echo "wp-config.php not found. Exiting."
    exit 1
  fi
  sleep 2
done

echo "Waiting for database to be ready..."
tries=60
until mariadb-admin ping -h db -u "${WORDPRESS_DB_USER}" -p"${WORDPRESS_DB_PASSWORD}" --silent --ssl=0 >/dev/null 2>&1; do
  tries=$((tries - 1))
  if [ "$tries" -le 0 ]; then
    echo "Database not ready. Exiting."
    exit 1
  fi
  sleep 2
done

cd /var/www/html

if ! wp core is-installed --allow-root >/dev/null 2>&1; then
  echo "Installing WordPress..."
  wp core install \
    --url="${WP_URL}" \
    --title="${WP_TITLE}" \
    --admin_user="${WP_ADMIN_USER}" \
    --admin_password="${WP_ADMIN_PASSWORD}" \
    --admin_email="${WP_ADMIN_EMAIL}" \
    --skip-email \
    --allow-root
fi

echo "Setting permalinks..."
wp rewrite structure "/%postname%/" --hard --allow-root

echo "WP CLI init complete."
