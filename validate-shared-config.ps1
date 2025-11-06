# PowerShell validation script to verify shared Database.php configuration

Write-Host "=== Shared Database.php Configuration Validation ===" -ForegroundColor Cyan

# Check if shared Database.php exists
Write-Host "1. Checking shared Database.php file..." -ForegroundColor Yellow
if (Test-Path "backend\app\core\Database.php") {
    Write-Host "   ✓ Shared Database.php exists at backend\app\core\Database.php" -ForegroundColor Green
} else {
    Write-Host "   ✗ Shared Database.php NOT found at backend\app\core\Database.php" -ForegroundColor Red
    exit 1
}

# Check that individual service Database.php files are removed
Write-Host "2. Checking individual service Database.php files are removed..." -ForegroundColor Yellow

if (-not (Test-Path "backend\auth-service\app\core\Database.php")) {
    Write-Host "   ✓ auth-service\app\core\Database.php correctly removed" -ForegroundColor Green
} else {
    Write-Host "   ✗ auth-service\app\core\Database.php still exists (should be removed)" -ForegroundColor Red
}

if (-not (Test-Path "backend\advise-service\app\core\Database.php")) {
    Write-Host "   ✓ advise-service\app\core\Database.php correctly removed" -ForegroundColor Green
} else {
    Write-Host "   ✗ advise-service\app\core\Database.php still exists (should be removed)" -ForegroundColor Red
}

# Check .env file
Write-Host "3. Checking .env file..." -ForegroundColor Yellow
if (Test-Path ".env") {
    Write-Host "   ✓ .env file exists" -ForegroundColor Green
    $envContent = Get-Content ".env" -Raw
    if ($envContent -match "DB_HOST=") {
        Write-Host "   ✓ DB_HOST defined in .env" -ForegroundColor Green
    } else {
        Write-Host "   ✗ DB_HOST not found in .env" -ForegroundColor Red
    }
    if ($envContent -match "DB_PORT=") {
        Write-Host "   ✓ DB_PORT defined in .env" -ForegroundColor Green
    } else {
        Write-Host "   ✗ DB_PORT not found in .env" -ForegroundColor Red
    }
} else {
    Write-Host "   ✗ .env file not found" -ForegroundColor Red
}

# Check docker-compose.yml
Write-Host "4. Checking docker-compose.yml configuration..." -ForegroundColor Yellow
if (Test-Path "docker-compose.yml") {
    Write-Host "   ✓ docker-compose.yml exists" -ForegroundColor Green
    $composeContent = Get-Content "docker-compose.yml" -Raw
    if ($composeContent -match '\$\{DB_NAME\}') {
        Write-Host "   ✓ Environment variables correctly referenced in docker-compose.yml" -ForegroundColor Green
    } else {
        Write-Host "   ✗ Environment variables not properly referenced in docker-compose.yml" -ForegroundColor Red
    }
} else {
    Write-Host "   ✗ docker-compose.yml not found" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Validation Complete ===" -ForegroundColor Cyan
Write-Host "To test the configuration:" -ForegroundColor White
Write-Host "1. Run: docker-compose up --build" -ForegroundColor White
Write-Host "2. Check container logs for database connection errors" -ForegroundColor White
Write-Host "3. Run: php backend/test-database-connection.php (in container or locally)" -ForegroundColor White