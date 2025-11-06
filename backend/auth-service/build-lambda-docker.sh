#!/bin/bash
# Build Lambda package using Docker

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BUILD_DIR="$SCRIPT_DIR/lambda-build"

echo "Building Lambda package for auth-service using Docker..."

# Clean previous build
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

# Build Docker image
echo "Building Docker image..."
docker build -f "$SCRIPT_DIR/Dockerfile.lambda-build" -t auth-service-builder "$SCRIPT_DIR"

# Create a temporary container and copy files
echo "Creating build container..."
CONTAINER_ID=$(docker create auth-service-builder)

# Copy built files from container
echo "Copying files from container..."
docker cp "$CONTAINER_ID:/build/." "$BUILD_DIR/"

# Verify vendor directory was copied
if [ ! -d "$BUILD_DIR/vendor" ]; then
    echo "ERROR: vendor directory not found in build!"
    docker rm "$CONTAINER_ID"
    exit 1
fi

# Clean up container
docker rm "$CONTAINER_ID"

# Copy shared Core and Middleware from parent app directory
echo "Copying shared Core and Middleware files..."
mkdir -p "$BUILD_DIR/app/Core"
mkdir -p "$BUILD_DIR/app/Middleware"
cp -r "$SCRIPT_DIR/../app/core/"* "$BUILD_DIR/app/Core/" 2>/dev/null || true
cp -r "$SCRIPT_DIR/../app/Middleware/"* "$BUILD_DIR/app/Middleware/"

echo "Verifying package contents..."
if [ ! -f "$BUILD_DIR/vendor/autoload.php" ]; then
    echo "ERROR: vendor/autoload.php not found!"
    exit 1
fi

echo "Lambda package prepared in: $BUILD_DIR"
echo "Package size:"
du -sh "$BUILD_DIR"

