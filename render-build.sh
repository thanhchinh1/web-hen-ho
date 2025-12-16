#!/usr/bin/env bash
# exit on error
set -o errexit

# Cài đặt dependencies
composer install --no-dev --optimize-autoloader

# Nếu cần chạy migrations
# php migrate.php