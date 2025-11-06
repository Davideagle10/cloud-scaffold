#!/bin/bash
# Prepare Lambda deployment package for auth-service

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BUILD_DIR="$SCRIPT_DIR/lambda-build"

echo "Preparing Lambda package for auth-service..."

# Clean previous build
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

# Copy service files
echo "Copying auth-service files..."
cp -r "$SCRIPT_DIR/api" "$BUILD_DIR/"
cp -r "$SCRIPT_DIR/app" "$BUILD_DIR/"
cp "$SCRIPT_DIR/composer.json" "$BUILD_DIR/"

# Copy shared files from parent app directory
echo "Copying shared Core and Middleware files..."
mkdir -p "$BUILD_DIR/app/Core"
mkdir -p "$BUILD_DIR/app/Middleware"
cp -r "$SCRIPT_DIR/../app/core/"* "$BUILD_DIR/app/Core/" 2>/dev/null || true
cp -r "$SCRIPT_DIR/../app/Middleware/"* "$BUILD_DIR/app/Middleware/"

# Install composer dependencies
echo "Installing composer dependencies..."
cd "$BUILD_DIR"
composer install --no-dev --optimize-autoloader --no-interaction

echo "Lambda package prepared in: $BUILD_DIR"

