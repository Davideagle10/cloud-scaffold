# Script de diagnóstico para verificar conectividad proxy -> advice-service
# Ayuda a identificar problemas de conectividad entre nginx proxy y el servicio advice

Write-Host "=== DIAGNÓSTICO DE CONECTIVIDAD PROXY -> ADVICE-SERVICE ===" -ForegroundColor Green

# 1. Verificar estado de contenedores
Write-Host "`n1. Estado de contenedores:" -ForegroundColor Yellow
docker ps --filter "name=nginx-proxy" --filter "name=advise-service" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

# 2. Verificar red de Docker
Write-Host "`n2. Red de Docker:" -ForegroundColor Yellow
$networkName = docker-compose ps --services | Where-Object { $_ -match "advise-service" }
if ($networkName) {
    $network = docker network ls --filter "name=talentotech2-easyapp" --format "{{.Name}}"
    if ($network) {
        Write-Host "Red encontrada: $network"
        docker network inspect $network --format '{{range .Containers}}{{.Name}}: {{.IPv4Address}}{{"\n"}}{{end}}'
    }
}

# 3. Verificar logs del proxy
Write-Host "`n3. Logs recientes del nginx-proxy (últimas 20 líneas):" -ForegroundColor Yellow
docker logs nginx-proxy --tail 20

# 4. Verificar logs del advise-service
Write-Host "`n4. Logs recientes del advise-service (últimas 20 líneas):" -ForegroundColor Yellow
docker logs advise-service --tail 20

# 5. Probar conectividad desde el proxy al advice-service
Write-Host "`n5. Probando conectividad desde proxy a advice-service:" -ForegroundColor Yellow
docker exec nginx-proxy sh -c "nc -z advise-service 9000 && echo 'Conexión exitosa al puerto 9000' || echo 'Error: No se puede conectar al puerto 9000'"

# 6. Verificar configuración de nginx
Write-Host "`n6. Configuración actual de nginx:" -ForegroundColor Yellow
docker exec nginx-proxy cat /etc/nginx/conf.d/nginx.conf

# 7. Probar una petición desde el proxy
Write-Host "`n7. Probando petición HTTP interna:" -ForegroundColor Yellow
docker exec nginx-proxy sh -c "curl -s -o /dev/null -w '%{http_code}' http://advise-service:9000/api/index.php || echo 'Error en la petición'"

# 8. Verificar procesos PHP-FPM en advice-service
Write-Host "`n8. Procesos PHP-FPM en advice-service:" -ForegroundColor Yellow
docker exec advise-service ps aux | grep php-fpm

# 9. Verificar configuración PHP-FPM
Write-Host "`n9. Configuración PHP-FPM listen:" -ForegroundColor Yellow
docker exec advise-service cat /usr/local/etc/php-fpm.d/www.conf | grep "listen ="

Write-Host "`n=== FIN DEL DIAGNÓSTICO ===" -ForegroundColor Green
Write-Host "Si hay problemas de conectividad, revisa:" -ForegroundColor Cyan
Write-Host "1. Que ambos contenedores estén en la misma red" -ForegroundColor White
Write-Host "2. Que PHP-FPM esté escuchando en 0.0.0.0:9000" -ForegroundColor White
Write-Host "3. Que nginx tenga la configuración fastcgi correcta" -ForegroundColor White
Write-Host "4. Que no haya firewalls bloqueando el puerto 9000" -ForegroundColor White