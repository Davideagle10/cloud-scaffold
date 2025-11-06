# Script para verificar que la corrección de copia funciona correctamente

Write-Host "=== Diagnóstico de Corrección de Copia en Dockerfiles ===" -ForegroundColor Cyan

function Test-DockerfilesCopyFix {
    Write-Host "`n🔧 Probando corrección de copia en Dockerfiles..." -ForegroundColor Yellow
    
    # Verificar que los Dockerfiles tienen la corrección
    Write-Host "`n1. 📋 Verificando sintaxis corregida en Dockerfiles:" -ForegroundColor Cyan
    
    $authContent = Get-Content "backend\auth.Dockerfile" -Raw
    $adviseContent = Get-Content "backend\advise.Dockerfile" -Raw
    
    if ($authContent -match "cp -r /build/\. /var/www/") {
        Write-Host "   ✅ auth.Dockerfile - Corrección aplicada" -ForegroundColor Green
    } else {
        Write-Host "   ❌ auth.Dockerfile - Aún usa sintaxis problemática" -ForegroundColor Red
        return $false
    }
    
    if ($adviseContent -match "cp -r /build/\. /var/www/") {
        Write-Host "   ✅ advise.Dockerfile - Corrección aplicada" -ForegroundColor Green
    } else {
        Write-Host "   ❌ advise.Dockerfile - Aún usa sintaxis problemática" -ForegroundColor Red
        return $false
    }
    
    return $true
}

function Test-ServiceContainerStructure {
    param([string]$ServiceName)
    
    Write-Host "`n🔍 Verificando estructura en contenedor $ServiceName..." -ForegroundColor Cyan
    
    try {
        # Verificar que el contenedor existe y está corriendo
        $containerStatus = docker ps --filter "name=$ServiceName" --format "{{.Status}}" 2>$null
        if (-not $containerStatus -or $containerStatus -notmatch "Up") {
            Write-Host "   ⚠️  Contenedor $ServiceName no está corriendo" -ForegroundColor Yellow
            return $false
        }
        
        # Verificar directorio principal
        $mainDir = docker exec $ServiceName ls -la /var/www/ 2>$null
        if ($mainDir) {
            Write-Host "   ✅ /var/www/ accesible" -ForegroundColor Green
        } else {
            Write-Host "   ❌ /var/www/ no accesible" -ForegroundColor Red
            return $false
        }
        
        # Verificar app/core existe
        $coreDir = docker exec $ServiceName test -d /var/www/app/core 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "   ✅ /var/www/app/core/ directorio existe" -ForegroundColor Green
        } else {
            Write-Host "   ❌ /var/www/app/core/ directorio NO existe" -ForegroundColor Red
            return $false
        }
        
        # Verificar Database.php específicamente
        $databaseFile = docker exec $ServiceName test -f /var/www/app/core/Database.php 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "   ✅ Database.php encontrado" -ForegroundColor Green
        } else {
            Write-Host "   ❌ Database.php NO encontrado" -ForegroundColor Red
            return $false
        }
        
        # Verificar contenido de Database.php
        $dbContent = docker exec $ServiceName head -2 /var/www/app/core/Database.php 2>$null
        if ($dbContent -match "<?php") {
            Write-Host "   ✅ Database.php tiene contenido válido" -ForegroundColor Green
        } else {
            Write-Host "   ❌ Database.php sin contenido válido" -ForegroundColor Red
            return $false
        }
        
        # Verificar que build directory fue limpiado
        $buildDir = docker exec $ServiceName test -d /build 2>$null
        if ($LASTEXITCODE -ne 0) {
            Write-Host "   ✅ Directorio /build correctamente eliminado" -ForegroundColor Green
        } else {
            Write-Host "   ⚠️  Directorio /build aún existe (debería estar eliminado)" -ForegroundColor Yellow
        }
        
        return $true
        
    } catch {
        Write-Host "   ❌ Error verificando $ServiceName : $_" -ForegroundColor Red
        return $false
    }
}

function Start-BuildAndTest {
    Write-Host "`n🏗️  Reconstruyendo servicios para probar corrección..." -ForegroundColor Yellow
    
    try {
        # Detener servicios existentes
        Write-Host "   Deteniendo servicios existentes..." -ForegroundColor Cyan
        docker-compose down 2>$null
        
        # Reconstruir servicios
        Write-Host "   Reconstruyendo servicios..." -ForegroundColor Cyan
        $buildResult = docker-compose up --build -d 2>&1
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "   ✅ Servicios reconstruidos exitosamente" -ForegroundColor Green
            
            # Esperar a que se inicien
            Write-Host "   Esperando que los servicios se inicien..." -ForegroundColor Cyan
            Start-Sleep -Seconds 15
            
            return $true
        } else {
            Write-Host "   ❌ Error en reconstrucción:" -ForegroundColor Red
            Write-Host $buildResult -ForegroundColor Red
            return $false
        }
    } catch {
        Write-Host "   ❌ Error en build: $_" -ForegroundColor Red
        return $false
    }
}

function Main {
    Write-Host "Iniciando diagnóstico de corrección de copia...`n" -ForegroundColor White
    
    # Paso 1: Verificar que la corrección está en los Dockerfiles
    if (-not (Test-DockerfilesCopyFix)) {
        Write-Host "`n❌ Los Dockerfiles no tienen la corrección aplicada" -ForegroundColor Red
        return
    }
    
    # Paso 2: Verificar Docker disponible
    try {
        docker --version > $null
        Write-Host "`n✅ Docker disponible" -ForegroundColor Green
    } catch {
        Write-Host "`n❌ Docker no disponible" -ForegroundColor Red
        return
    }
    
    # Paso 3: Reconstruir servicios
    if (-not (Start-BuildAndTest)) {
        Write-Host "`n❌ Error en reconstrucción de servicios" -ForegroundColor Red
        return
    }
    
    # Paso 4: Verificar estructura en contenedores
    $authOk = Test-ServiceContainerStructure "auth-service"
    $adviseOk = Test-ServiceContainerStructure "advise-service" 
    
    # Resultado final
    Write-Host "`n=== RESULTADO FINAL ===" -ForegroundColor Cyan
    if ($authOk -and $adviseOk) {
        Write-Host "🎉 ¡CORRECCIÓN EXITOSA!" -ForegroundColor Green
        Write-Host "   • app/core/ se copia correctamente" -ForegroundColor Green
        Write-Host "   • Database.php está disponible en ambos servicios" -ForegroundColor Green
        Write-Host "   • Limpieza de directorio temporal funciona" -ForegroundColor Green
    } else {
        Write-Host "⚠️  CORRECCIÓN PARCIAL" -ForegroundColor Yellow
        Write-Host "   • Algunos servicios pueden tener problemas" -ForegroundColor Yellow
        Write-Host "   • Verificar logs: docker-compose logs [service-name]" -ForegroundColor Yellow
    }
    
    Write-Host "`n📋 Comandos útiles para debug adicional:" -ForegroundColor Cyan
    Write-Host "   docker exec -it auth-service ls -la /var/www/app/core/" -ForegroundColor White
    Write-Host "   docker exec -it advise-service ls -la /var/www/app/core/" -ForegroundColor White
    Write-Host "   docker-compose logs auth-service" -ForegroundColor White
    Write-Host "   docker-compose logs advise-service" -ForegroundColor White
}

# Ejecutar diagnóstico
Main