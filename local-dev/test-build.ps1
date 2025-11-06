# Script para probar la configuración de build de Angular en Windows
# Uso: .\test-build.ps1

Write-Host "🚀 Iniciando test de build de Angular..." -ForegroundColor Green

# Navegar al directorio frontend
Set-Location frontend

Write-Host "📦 Instalando dependencias..." -ForegroundColor Yellow
npm install

Write-Host "🧹 Limpiando build anterior..." -ForegroundColor Yellow
if (Test-Path "dist") {
    Remove-Item -Recurse -Force "dist"
}

Write-Host "🔨 Construyendo aplicación..." -ForegroundColor Yellow
& .\node_modules\.bin\ng build --configuration production

Write-Host "📁 Verificando estructura del build..." -ForegroundColor Yellow
Write-Host ""
Write-Host "=== Estructura inicial después del build ===" -ForegroundColor Cyan
Get-ChildItem -Path "dist" -Recurse | Select-Object -First 10 | Format-Table

# Si existe la carpeta browser, mover todo a la raíz
if (Test-Path "dist\browser") {
    Write-Host "📁 Carpeta browser encontrada, moviendo archivos a la raíz..." -ForegroundColor Yellow
    
    # Crear directorio temporal
    New-Item -ItemType Directory -Path "dist_temp" -Force | Out-Null
    
    # Mover contenido de browser a temporal
    Move-Item -Path "dist\browser\*" -Destination "dist_temp\" -Force
    
    # Limpiar dist y mover archivos de vuelta
    Remove-Item -Path "dist\*" -Recurse -Force
    Move-Item -Path "dist_temp\*" -Destination "dist\" -Force
    Remove-Item -Path "dist_temp" -Force
    
    Write-Host "✅ Archivos movidos a la raíz" -ForegroundColor Green
} else {
    Write-Host "✅ Los archivos ya están en la raíz" -ForegroundColor Green
}

Write-Host "🔍 Verificando resultado..." -ForegroundColor Yellow
Write-Host ""
Write-Host "=== Contenido de dist/ ===" -ForegroundColor Cyan
Get-ChildItem -Path "dist" -Force | Format-Table

Write-Host ""
Write-Host "=== Verificando estructura ===" -ForegroundColor Cyan
if (Test-Path "dist\index.html") {
    Write-Host "✅ index.html encontrado en la raíz" -ForegroundColor Green
} else {
    Write-Host "❌ index.html NO encontrado en la raíz" -ForegroundColor Red
    Write-Host "Estructura completa:" -ForegroundColor Red
    Get-ChildItem -Path "dist" -Recurse | Select-Object -First 20 | Format-Table
    exit 1
}

Write-Host ""
Write-Host "=== Archivos principales ===" -ForegroundColor Cyan
Get-ChildItem -Path "dist\*.html", "dist\*.js", "dist\*.css" -ErrorAction SilentlyContinue | Format-Table

Write-Host ""
Write-Host "✅ Build completado exitosamente!" -ForegroundColor Green
Write-Host "📁 Los archivos están listos en frontend/dist/" -ForegroundColor Green
Write-Host ""
Write-Host "🚀 Para subir a S3:" -ForegroundColor Cyan
Write-Host "aws s3 sync frontend/dist/ s3://repository-terraform-states-prod --delete" -ForegroundColor White

# Volver al directorio raíz
Set-Location ..