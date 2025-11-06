#!/bin/bash
# Script para reconstruir y reiniciar los servicios con las configuraciones corregidas

echo "=== RECONSTRUYENDO SERVICIOS CON CONFIGURACIÓN CORREGIDA ==="

# Detener los servicios afectados
echo "1. Deteniendo servicios..."
docker-compose stop nginx-proxy advise-service auth-service

# Reconstruir las imágenes con las nuevas configuraciones
echo "2. Reconstruyendo imágenes..."
docker-compose build nginx-proxy advise-service auth-service

# Reiniciar los servicios
echo "3. Reiniciando servicios..."
docker-compose up -d nginx-proxy advise-service auth-service

echo "4. Esperando que los servicios estén listos..."
sleep 10

# Verificar estado
echo "5. Verificando estado de los contenedores:"
docker ps --filter "name=nginx-proxy" --filter "name=advise-service" --filter "name=auth-service"

echo "6. Verificando logs del nginx-proxy:"
docker logs nginx-proxy --tail 10

echo "7. Verificando logs del advise-service:"
docker logs advise-service --tail 10

echo "=== SERVICIOS ACTUALIZADOS ==="
echo "Prueba hacer una petición a: http://localhost:8080/api/v1/requests"