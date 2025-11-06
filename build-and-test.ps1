# Script para construir y validar los servicios optimizados

Write-Host "=== Build y Validación de Servicios Optimizados ===" -ForegroundColor Cyan

# Función para verificar si Docker está corriendo
function Test-DockerRunning {
    try {
        $result = docker --version 2>$null
        if ($result) {
            Write-Host "✅ Docker está disponible: $result" -ForegroundColor Green
            return $true
        }
    }
    catch {
        Write-Host "❌ Docker no está disponible o no está corriendo" -ForegroundColor Red
        return $false
    }
}

# Función para verificar archivos necesarios
function Test-RequiredFiles {
    $files = @(
        ".env",
        "docker-compose.yml",
        "backend/auth.Dockerfile",
        "backend/advise.Dockerfile",
        "backend/app/core/Database.php"
    )
    
    Write-Host "`n🔍 Verificando archivos necesarios..." -ForegroundColor Yellow
    $allExists = $true
    
    foreach ($file in $files) {
        if (Test-Path $file) {
            Write-Host "   ✅ $file" -ForegroundColor Green
        } else {
            Write-Host "   ❌ $file - NO ENCONTRADO" -ForegroundColor Red
            $allExists = $false
        }
    }
    
    return $allExists
}

# Función para construir servicios
function Start-ServicesBuild {
    Write-Host "`n🏗️  Construyendo servicios..." -ForegroundColor Yellow
    
    try {
        Write-Host "Ejecutando: docker-compose up --build -d" -ForegroundColor Cyan
        $result = docker-compose up --build -d 2>&1
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "✅ Servicios construidos exitosamente" -ForegroundColor Green
            return $true
        } else {
            Write-Host "❌ Error en la construcción:" -ForegroundColor Red
            Write-Host $result -ForegroundColor Red
            return $false
        }
    }
    catch {
        Write-Host "❌ Error ejecutando docker-compose: $_" -ForegroundColor Red
        return $false
    }
}

# Función para verificar servicios corriendo
function Test-ServicesRunning {
    Write-Host "`n🔍 Verificando servicios corriendo..." -ForegroundColor Yellow
    
    $services = @("auth-service", "advise-service", "mysql-db")
    $allRunning = $true
    
    foreach ($service in $services) {
        try {
            $status = docker ps --filter "name=$service" --format "table {{.Names}}\t{{.Status}}" | Select-Object -Skip 1
            if ($status -and $status -match "Up") {
                Write-Host "   ✅ $service - Corriendo" -ForegroundColor Green
            } else {
                Write-Host "   ❌ $service - No corriendo o con problemas" -ForegroundColor Red
                $allRunning = $false
            }
        }
        catch {
            Write-Host "   ❌ $service - Error verificando estado" -ForegroundColor Red
            $allRunning = $false
        }
    }
    
    return $allRunning
}

# Función para verificar estructura de archivos en contenedores
function Test-ContainerStructure {
    Write-Host "`n🔍 Verificando estructura en contenedores..." -ForegroundColor Yellow
    
    $services = @("auth-service", "advise-service")
    $allGood = $true
    
    foreach ($service in $services) {
        Write-Host "   Verificando $service..." -ForegroundColor Cyan
        
        try {
            # Verificar directorio principal
            $mainDir = docker exec $service ls -la /var/www/ 2>$null
            if ($mainDir) {
                Write-Host "     ✅ /var/www/ existe" -ForegroundColor Green
            } else {
                Write-Host "     ❌ /var/www/ no accesible" -ForegroundColor Red
                $allGood = $false
            }
            
            # Verificar directorio core compartido
            $coreDir = docker exec $service ls -la /var/www/app/core/ 2>$null
            if ($coreDir -match "Database.php") {
                Write-Host "     ✅ Database.php compartido disponible" -ForegroundColor Green
            } else {
                Write-Host "     ❌ Database.php compartido no encontrado" -ForegroundColor Red
                $allGood = $false
            }
            
            # Verificar que no existe directorio build temporal
            $buildDir = docker exec $service ls -la /build 2>$null
            if (-not $buildDir) {
                Write-Host "     ✅ Directorio temporal /build limpiado" -ForegroundColor Green
            } else {
                Write-Host "     ⚠️  Directorio temporal /build aún existe" -ForegroundColor Yellow
            }
            
        }
        catch {
            Write-Host "     ❌ Error verificando $service" -ForegroundColor Red
            $allGood = $false
        }
    }
    
    return $allGood
}

# Función principal
function Main {
    Write-Host "Iniciando validación completa de servicios optimizados...`n" -ForegroundColor White
    
    # Paso 1: Verificar Docker
    if (-not (Test-DockerRunning)) {
        Write-Host "`n❌ Abortando: Docker no está disponible" -ForegroundColor Red
        return
    }
    
    # Paso 2: Verificar archivos
    if (-not (Test-RequiredFiles)) {
        Write-Host "`n❌ Abortando: Faltan archivos necesarios" -ForegroundColor Red
        return
    }
    
    # Paso 3: Construir servicios
    if (-not (Start-ServicesBuild)) {
        Write-Host "`n❌ Abortando: Error en la construcción" -ForegroundColor Red
        return
    }
    
    # Esperar un momento para que los servicios se inicien
    Write-Host "`n⏱️  Esperando que los servicios se inicien..." -ForegroundColor Yellow
    Start-Sleep -Seconds 10
    
    # Paso 4: Verificar servicios corriendo
    if (-not (Test-ServicesRunning)) {
        Write-Host "`n⚠️  Algunos servicios pueden tener problemas" -ForegroundColor Yellow
    }
    
    # Paso 5: Verificar estructura en contenedores
    if (Test-ContainerStructure) {
        Write-Host "`n✅ ¡Validación completa exitosa!" -ForegroundColor Green
    } else {
        Write-Host "`n⚠️  Validación completa con advertencias" -ForegroundColor Yellow
    }
    
    # Información adicional
    Write-Host "`n📋 Comandos útiles:" -ForegroundColor Cyan
    Write-Host "  docker-compose logs auth-service" -ForegroundColor White
    Write-Host "  docker-compose logs advise-service" -ForegroundColor White
    Write-Host "  docker exec -it auth-service php /var/www/../test-database-connection.php" -ForegroundColor White
    Write-Host "  docker-compose down" -ForegroundColor White
}

# Ejecutar función principal
Main