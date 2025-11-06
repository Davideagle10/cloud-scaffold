# Diagnóstico del Problema de Copia en Dockerfiles

## 🐛 **Problema Identificado**

### **Causa Raíz:**
El comando `cp -r /build/* /var/www/` no copia todos los archivos como se esperaba debido al comportamiento del wildcard `*` en algunos casos.

### **Diferencia entre comandos:**

#### ❌ **Problemático:**
```bash
cp -r /build/* /var/www/
```
- El `*` puede no expandir correctamente todos los archivos
- Puede tener problemas con archivos ocultos o estructura de directorios específica
- No garantiza copia completa en todos los casos

#### ✅ **Corregido:**
```bash
cp -r /build/. /var/www/
```
- El `/build/.` copia **todo el contenido** del directorio build
- Incluye todos los archivos, directorios y subdirectorios
- Comportamiento más predecible y confiable

## 🔍 **Script de Diagnóstico**

Para verificar que el problema está resuelto, usa este script:

```powershell
# Construir un solo servicio para debug
docker-compose up --build auth-service -d

# Verificar estructura en build (si aún existe)
docker exec -it auth-service ls -la /build 2>/dev/null || echo "Build directory cleaned (expected)"

# Verificar estructura completa en /var/www
Write-Host "=== Estructura en /var/www ===" -ForegroundColor Cyan
docker exec -it auth-service ls -la /var/www/

# Verificar específicamente app/core
Write-Host "`n=== Contenido de app/core ===" -ForegroundColor Cyan
docker exec -it auth-service ls -la /var/www/app/core/

# Verificar que Database.php existe
Write-Host "`n=== Verificando Database.php ===" -ForegroundColor Cyan
docker exec -it auth-service cat /var/www/app/core/Database.php | head -5

# Verificar autoloader
Write-Host "`n=== Verificando autoloader ===" -ForegroundColor Cyan
docker exec -it auth-service ls -la /var/www/vendor/composer/

Write-Host "`n✅ Si todos los comandos anteriores funcionan, el problema está resuelto" -ForegroundColor Green
```

## 📋 **Checklist de Verificación**

Después de aplicar la corrección, verifica:

- [ ] ✅ Directorio `/var/www/app/core/` existe en el contenedor
- [ ] ✅ Archivo `/var/www/app/core/Database.php` existe y tiene contenido
- [ ] ✅ Archivo `/var/www/vendor/autoload.php` existe
- [ ] ✅ Directorio `/build` ha sido eliminado (limpieza correcta)
- [ ] ✅ Permisos correctos en `/var/www` (www-data:www-data)

## 🔧 **Cambios Aplicados**

### En `auth.Dockerfile`:
```dockerfile
# ANTES (Problemático)
RUN cp -r /build/* /var/www/

# DESPUÉS (Corregido)  
RUN cp -r /build/. /var/www/ && \
    rm -rf /build
```

### En `advise.Dockerfile`:
```dockerfile
# ANTES (Problemático)
RUN cp -r /build/* /var/www/ && \
    rm -rf /build

# DESPUÉS (Corregido)
RUN cp -r /build/. /var/www/ && \
    rm -rf /build
```

## 🎯 **Por qué funciona la corrección:**

1. **`/build/.`** significa "todo el contenido del directorio build"
2. **Copia completa** de todos los archivos y directorios
3. **Comportamiento consistente** en diferentes sistemas
4. **No depende** de la expansión del wildcard `*`

## 🧪 **Comandos de Prueba Rápida**

```bash
# Rebuild y test
docker-compose down
docker-compose up --build -d

# Verificación rápida
docker exec -it auth-service ls -la /var/www/app/core/
docker exec -it advise-service ls -la /var/www/app/core/
```

Si ambos comandos muestran el contenido de `app/core/` con `Database.php` y otros archivos, ¡el problema está resuelto! ✅