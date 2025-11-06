# Script de validación para la nueva estructura de Dockerfiles
# Verifica que los Dockerfiles estén usando el patrón de build -> copy

Write-Host "=== Validación de Dockerfiles Optimizados ===" -ForegroundColor Cyan

function Test-DockerfilePattern {
    param(
        [string]$FilePath,
        [string]$ServiceName
    )
    
    Write-Host "`n${ServiceName} Dockerfile Validation:" -ForegroundColor Yellow
    
    if (-not (Test-Path $FilePath)) {
        Write-Host "   ✗ $FilePath no encontrado" -ForegroundColor Red
        return $false
    }
    
    $content = Get-Content $FilePath -Raw
    
    # Verificar patrones esperados
    $patterns = @{
        "WORKDIR /build" = "✓ Usa directorio de build temporal"
        "composer.json" = "✓ Copia composer.json primero (mejor caching)"
        "composer install" = "✓ Instala dependencias"
        "COPY app/core/" = "✓ Copia archivos core compartidos"
        "composer dump-autoload --optimize" = "✓ Genera autoloader optimizado"
        "WORKDIR /var/www" = "✓ Usa directorio de trabajo final"
        "cp -r /build/" = "✓ Copia todo desde build a www"
        "rm -rf /build" = "✓ Limpia directorio temporal"
    }
    
    $allPassed = $true
    foreach ($pattern in $patterns.Keys) {
        if ($content -match [regex]::Escape($pattern)) {
            Write-Host "   $($patterns[$pattern])" -ForegroundColor Green
        } else {
            Write-Host "   ✗ Falta: $pattern" -ForegroundColor Red
            $allPassed = $false
        }
    }
    
    return $allPassed
}

# Validar ambos Dockerfiles
$authResult = Test-DockerfilePattern "backend\auth.Dockerfile" "Auth Service"
$adviseResult = Test-DockerfilePattern "backend\advise.Dockerfile" "Advise Service"

Write-Host "`n=== Resumen de Validación ===" -ForegroundColor Cyan

if ($authResult -and $adviseResult) {
    Write-Host "✅ Todos los Dockerfiles están configurados correctamente" -ForegroundColor Green
    Write-Host "`nEstructura optimizada:" -ForegroundColor White
    Write-Host "1. 📦 Instala dependencias del sistema" -ForegroundColor White
    Write-Host "2. 🏗️  Prepara aplicación en /build" -ForegroundColor White
    Write-Host "3. 📋 Copia composer.json (mejor caching)" -ForegroundColor White
    Write-Host "4. 📚 Instala dependencias de Composer" -ForegroundColor White
    Write-Host "5. 📁 Copia archivos del servicio" -ForegroundColor White
    Write-Host "6. 🔗 Copia archivos core compartidos" -ForegroundColor White
    Write-Host "7. ⚡ Optimiza autoloader" -ForegroundColor White
    Write-Host "8. 🚀 Copia todo a /var/www y limpia" -ForegroundColor White
    
    Write-Host "`nPara probar:" -ForegroundColor Yellow
    Write-Host "docker-compose up --build" -ForegroundColor Cyan
} else {
    Write-Host "❌ Algunos Dockerfiles necesitan corrección" -ForegroundColor Red
}

Write-Host "`n=== Beneficios de la Nueva Estructura ===" -ForegroundColor Cyan
Write-Host "• 🚀 Mejor rendimiento: preparación optimizada antes de copia final" -ForegroundColor Green
Write-Host "• 🎯 Mejor caching: composer.json copiado primero" -ForegroundColor Green
Write-Host "• 🧹 Más limpio: directorio temporal eliminado después del build" -ForegroundColor Green
Write-Host "• 📋 Más organizado: pasos claramente separados" -ForegroundColor Green
Write-Host "• 🔧 Más mantenible: estructura consistente entre servicios" -ForegroundColor Green