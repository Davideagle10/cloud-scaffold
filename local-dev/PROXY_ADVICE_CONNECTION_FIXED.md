# Solución al problema de conectividad Proxy -> Advice-Service

## Problemas identificados y corregidos:

### 1. **PHP-FPM configuración de red**
- **Problema**: PHP-FPM estaba configurado para escuchar solo en `127.0.0.1:9000` (localhost)
- **Solución**: Cambiar a `0.0.0.0:9000` para permitir conexiones externas
- **Archivos modificados**: 
  - `backend/php-fpm-fixed.conf`
  - `backend/advise.Dockerfile`
  - `backend/auth.Dockerfile`

### 2. **Archivos de clase faltantes**
- **Problema**: La clase `JwtMiddleware` no estaba disponible en los contenedores
- **Solución**: Copiar el directorio `app/Middleware/` en los Dockerfiles
- **Archivos modificados**: 
  - `backend/advise.Dockerfile`
  - `backend/auth.Dockerfile`

### 3. **Enrutamiento de URLs con trailing slash**
- **Problema**: El código buscaba rutas exactas `/api/v1/requests` pero nginx enviaba `/api/v1/requests/`
- **Solución**: Modificar la lógica de enrutamiento para manejar ambos casos
- **Archivos modificados**: 
  - `backend/advise-service/api/index.php`

### 4. **Configuración de nginx mejorada**
- **Problema**: Configuración FastCGI básica sin timeouts ni logs de debug
- **Solución**: Agregar timeouts y mejorar la configuración FastCGI
- **Archivos modificados**: 
  - `proxy/nginx.conf`

### 5. **Restricciones de IP en PHP-FPM**
- **Problema**: `listen.allowed_clients` bloqueaba conexiones desde la red Docker
- **Solución**: Remover restricciones temporalmente para permitir conexiones
- **Archivos modificados**: 
  - `backend/php-fpm-fixed.conf`

## Verificación del seguimiento:

### Logs de debugging agregados:
- Información de peticiones entrantes (método, URI, headers)
- Procesamiento de rutas y controladores
- Estado del middleware JWT

### Flujo de petición verificado:
1. **Cliente** → `localhost:8080/api/v1/requests/`
2. **nginx-proxy** → enruta a `advise-service:9000` via FastCGI
3. **advise-service** → procesa la petición en PHP
4. **Respuesta** → JSON válido de vuelta al cliente

### Comandos de prueba:
```bash
# Petición GET (devuelve 401 sin JWT - comportamiento correcto)
curl -X GET "http://localhost:8080/api/v1/requests/" -H "Accept: application/json"

# Verificar logs en tiempo real
docker logs advise-service --tail 10 -f
docker logs nginx-proxy --tail 10 -f
```

## Estado actual:
✅ **Conectividad proxy -> advice-service funcionando correctamente**
✅ **Enrutamiento de peticiones funcional**
✅ **Seguimiento de logs implementado**
✅ **Respuestas JSON válidas**

El sistema ahora puede recibir y procesar peticiones desde el proxy al servicio advice. El siguiente paso sería configurar JWT tokens válidos para testing completo.