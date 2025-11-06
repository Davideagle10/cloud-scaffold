#!/bin/bash
# Validation script to verify shared Database.php configuration

echo "=== Shared Database.php Configuration Validation ==="

# Check if shared Database.php exists
echo "1. Checking shared Database.php file..."
if [ -f "backend/app/core/Database.php" ]; then
    echo "   ✓ Shared Database.php exists at backend/app/core/Database.php"
else
    echo "   ✗ Shared Database.php NOT found at backend/app/core/Database.php"
    exit 1
fi

# Check that individual service Database.php files are removed
echo "2. Checking individual service Database.php files are removed..."

if [ ! -f "backend/auth-service/app/core/Database.php" ]; then
    echo "   ✓ auth-service/app/core/Database.php correctly removed"
else
    echo "   ✗ auth-service/app/core/Database.php still exists (should be removed)"
fi

if [ ! -f "backend/advise-service/app/core/Database.php" ]; then
    echo "   ✓ advise-service/app/core/Database.php correctly removed"
else
    echo "   ✗ advise-service/app/core/Database.php still exists (should be removed)"
fi

# Check .env file
echo "3. Checking .env file..."
if [ -f ".env" ]; then
    echo "   ✓ .env file exists"
    if grep -q "DB_HOST=" .env; then
        echo "   ✓ DB_HOST defined in .env"
    else
        echo "   ✗ DB_HOST not found in .env"
    fi
    if grep -q "DB_PORT=" .env; then
        echo "   ✓ DB_PORT defined in .env"
    else
        echo "   ✗ DB_PORT not found in .env"
    fi
else
    echo "   ✗ .env file not found"
fi

# Check docker-compose.yml
echo "4. Checking docker-compose.yml configuration..."
if [ -f "docker-compose.yml" ]; then
    echo "   ✓ docker-compose.yml exists"
    if grep -q "\${DB_NAME}" docker-compose.yml; then
        echo "   ✓ Environment variables correctly referenced in docker-compose.yml"
    else
        echo "   ✗ Environment variables not properly referenced in docker-compose.yml"
    fi
else
    echo "   ✗ docker-compose.yml not found"
fi

echo ""
echo "=== Validation Complete ==="
echo "To test the configuration:"
echo "1. Run: docker-compose up --build"
echo "2. Check container logs for database connection errors"
echo "3. Run: php backend/test-database-connection.php (in container or locally)"