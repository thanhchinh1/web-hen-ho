#!/usr/bin/env bash

# Lấy PORT từ environment variable
PORT=${PORT:-10000}

# Start PHP built-in server
php -S 0.0.0.0:$PORT