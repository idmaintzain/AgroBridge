#!/bin/sh
set -e
cd "$(dirname "$0")/.."

if mysqladmin --socket=/tmp/agrobridge.sock ping --silent >/dev/null 2>&1; then
  echo "MySQL is already accepting connections on port 3307."
  exit 0
fi

if [ ! -d storage/mysql-data/mysql ]; then
  mysqld --initialize-insecure --datadir="$PWD/storage/mysql-data" --user="$(whoami)"
  mysql --socket=/tmp/agrobridge.sock --user=root -e "CREATE DATABASE IF NOT EXISTS agrobridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || true
fi

mysqld \
  --datadir="$PWD/storage/mysql-data" \
  --port=3307 \
  --socket=/tmp/agrobridge.sock \
  --pid-file="$PWD/storage/mysql.pid" \
  --bind-address=127.0.0.1 \
  --mysqlx=0 \
  --daemonize

sleep 2
mysql --socket=/tmp/agrobridge.sock -u root -e "CREATE DATABASE IF NOT EXISTS agrobridge CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "MySQL is ready on 127.0.0.1:3307 (database agrobridge, user root, no password)."
