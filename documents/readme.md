# 🌐 Easy English App (Taller 1)

> **URL del Proyecto**: https://cursoingles.gt.tc/?i=1

## 📝 Descripción del Proyecto y Sector Elegido

**Nombre del Proyecto:** Easy English App  
**Sector Elegido:** Servicios - Asesorías de Inglés

Este proyecto consiste en una aplicación desarrollada en **PHP** y **MySQL** que tiene como objetivo registrar clientes interesados en asesorías de inglés y permitir la administración de estos contactos a través de un Panel de Control.

### 🎯 Objetivo Principal
Desarrollar una aplicación web PHP + HTML destinada a registrar clientes interesados en asesorías de inglés y administrar los contactos desde un Panel de Control.

### **Historias de Usuario Clave**
- Como **Usuario final**, quiero registrarme, para ser contactado para asesoría sobre el curso de inglés.
- Como **Usuario**, quiero solicitar darme de baja de la base de datos, para no recibir más información sobre los cursos.
- Como **Administrador/Asesor**, quiero consultar y ver la lista de contactos, para filtrar cuáles necesito contactar.

## 👥 Integrantes y Roles

El equipo cuenta con **6 integrantes**, por lo que se ha optado por combinar el rol de **Documentador/Presentador** con el **Líder / Coordinador** (ScrumMaster) para cubrir las responsabilidades completas.

| Integrante | Rol Asignado | Responsabilidades Específicas | Rol de Origen en Documento |
| :--- | :--- | :--- | :--- |
| **Alejandro Parra** | Líder / Coordinador & Documentador / Presentador | Coordina tareas, verifica checklist, sube entrega final, redacta `README.md` y prepara presentación. | ScrumMaster |
| **Yeison Liscano** | Desarrollador Backend | Valida *inputs*, implementa *prepared statements* y gestiona la lógica de servidor. | Secretario - Dev |
| **Katerine Restrepo** | Desarrollador Frontend / UI | Mejora formularios, estilos y organiza *assets* estáticos. | Arquitect On prime |
| **Daniel Mejia** | Administrador de BD (DBA) | Exporta `dump.sql`, verifica integridad y crea registros de prueba. | DB Master |
| **David Aguilar** | DevOps / Deployment | Sube archivos a InfinityFree, configura `db_connect.php` y estructura `public_html`. | Arquitect Cloud |
| **Ivan Rosero** | QA / Tester | Realiza pruebas, documenta errores y verifica correcciones. | Analista |

### 🛠️ Stack Tecnológico
- **Frontend**: HTML5
- **Backend**: PHP
- **Base de Datos**: MySQL
- **Hosting**: InfinityFree
- **Herramientas**: Visual Studio Code, FileZilla FTP, phpMyAdmin, GitHub

## 🚀 Instrucciones para Ejecutar Localmente

**Stack Tecnológico:** HTML, PHP, MySQL

### Requisitos
1. Servidor local (ej: XAMPP, MAMP, WAMP) con soporte para **PHP** y **MySQL**.
2. Importar la base de datos (`dump.sql`) a su servidor MySQL local.

### Configuración de Conexión (`db_connect.php`)

Para ejecutar la aplicación localmente, debe modificar el archivo `db_connect.php` con las credenciales de su entorno local (ej: usuario: `root`, password: `''`).

**Valores de ejemplo (de hosting) a ser reemplazados por sus valores locales:**

| Variable / Detalle | Valor a Reemplazar (Hosting) | **Valor Local Sugerido** |
| :--- | :--- | :--- |
| `DB_HOST` | `infinityfree.com` | `localhost` |
12537| `DB_PASSWORD` | `**********` | `''` (vacío) |
| `DB_NAME` | `*******` | `******` |
| `DB_PORT` | `*******` | `********` |

### Despliegue en InfinityFree
1. Subir los archivos a la carpeta `htdocs` o `public_html` en InfinityFree
2. Configurar la conexión en `db_connect.php` con las credenciales del hosting

### Acceso al Sistema
- **Sitio público**: https://cursoingles.gt.tc/?i=1
- **Panel de administración**: https://cursoingles.gt.tc/admin/login.php

## 📊 Estructura de la Base de Datos

### Tablas Principales

#### Clientes Potenciales
```sql
- id
- nombres
- correos  
- telefono
- fecha_creacion
- check_contacto
- fecha_contacto
- usuarios
- password
- asesor_asignado (FK)
- lista_idiomas_aprender (inglés, francés, portugués, etc)
```

#### Resultados Contacto
```sql
- id
- id_asesor
- cliente_asignado
- fecha_contacto
- check_eliminar (darse de baja)
- notas
```

#### Asesores
```sql
- id
- nombre
- apellido
- activo_en_plataforma
```

## 🔧 Funcionalidades del Sistema

### Para Usuarios Finales
- ✅ **Registro de asesorías**: Formulario de inscripción
- ✅ **Selección de idiomas**: Multi-check para idiomas a aprender
- ✅ **Solicitud de baja**: Opción para darse de baja
- ✅ **Actualización de datos**: Solicitar corrección de información

### Para Administradores/Asesores
- ✅ **Autenticación segura**: Login con usuario y contraseña
- ✅ **Panel de control**: Listado de solicitudes con filtros
- ✅ **Gestión de contactos**: Ver, filtrar y contactar clientes
- ✅ **Filtros avanzados**: Por nombre, correo, idiomas, fecha, etc.
- ✅ **Gestión de registros**: Eliminar, corregir datos

## 🔒 Consideraciones de Seguridad

### Medidas Implementadas
- **Validación y sanitización** de campos
- **Prepared statements** para evitar inyección SQL
- **Hash + salt** para contraseñas
- **HTTPS** y configuración segura de sesión
- **CAPTCHA** en formularios para proteger contra bots
- **Manejo seguro de cookies** y sesiones

### Vulnerabilidades Identificadas
- Ataque de denegación de servicio (DoS)
- Inyección SQL en login, solicitudes y filtros
- Manejo incorrecto de sesiones
- Mal manejo de errores

## ☁️ Migración a Cloud (AWS)

### Beneficios de la Migración
- **Escalabilidad**: Horizontal y vertical
- **Seguridad**: IAM, VPC, Service Control Policies
- **Disponibilidad**: Availability zones y replicación
- **Automatización**: Creación de ambientes
- **Personalización**: Libre elección de tecnologías

### Opciones de Migración

#### Opción 1: Básica
- VPC + Security Groups
- Amazon Lightsail + DB MySQL
- RDS MySQL pequeña (db.t3.micro)

#### Opción 2: Intermedia  
- VPC + Security Groups
- EC2 con ASG + DB RDS MySQL multi-AZ

#### Opción 3: Avanzada
- VPC + Security Groups
- EC2 con ASG + Aurora MySQL multi-AZ + ELB + Route53

#### Opción 4: Serverless
- VPC + Security Groups
- ECS Fargate + Aurora Serverless v2
- PHP en Docker → ECR + S3 + CloudFront

#### Opción 5: Escalabilidad Avanzada
- EKS + Karpenter + Aurora MySQL Serverless v2
- S3 + CloudFront + ELB Ingress Controller + Route53

## 📁 Archivos Entregados

- **codigo.zip** – Código completo del proyecto
- **dump.sql** – Base de datos exportada  
- **qa-report.md** – Reporte de pruebas realizadas
- **Carpeta capturas/** – Evidencias gráficas

## 🔗 Evidencias (Enlaces y Capturas)

| Módulo | Enlace (Ejecución en Hosting) | Capturas de Pantalla (Local/Producción) |
| :--- | :--- | :--- |
| Formulario de Registro (HU1) | [https://cursoingles.gt.tc/?i=1](https://cursoingles.gt.tc/?i=1) | *(Insertar capturas del formulario de registro)* |
| Login de Administración | [https://cursoingles.gt.tc/admin/login.php](https://cursoingles.gt.tc/admin/login.php) | *(Insertar capturas del login de administración)* |

## 📜 Changelog (Qué hizo cada integrante)

| Integrante | Tareas Realizadas (Ejemplos) |
| :--- | :--- |
| Alejandro Parra | Finalización de la redacción del `README.md`, verificación del *checklist* final. |
| Yeison Liscano | Implementación de sanitización de campos, uso de *prepared statements* para el registro de asesorías. |
| Katerine Restrepo | Diseño y mejora de estilos CSS para los formularios de registro y login. |
| Daniel Mejia | Creación del esquema MySQL (`Clientes potenciales`, `Resultados contacto`, `Asesores`), exportación de `dump.sql`. |
| David Aguilar | Despliegue en InfinityFree, configuración de las credenciales en `db_connect.php` del entorno de producción. |
| Ivan Rosero | Documentación de errores en el login (manejo incorrecto de sesiones), verificación de datos. |

## ❓ Respuestas a las Preguntas de Seguridad y Migración a Cloud

### 🔒 Seguridad

El proyecto contempla las siguientes consideraciones de seguridad:

- **Validación y Sanitización:** Limpieza de campos y valores para evitar la inyección de código.
- **Consultas SQL:** Se contempla *Parameterize all SQL queries* para evitar la inyección SQL.
- **Autenticación:** Uso de **Hash + salt Password** para el *login* y uso de **HTTPS** y configuración segura de sesión.
- **Contra Bots/Ataques Automatizados:** Implementación de **captcha** en el formulario de solicitud de cursos y *login*.
- **Denegación de Servicio (DoS):** Se contempla la posibilidad de reducir la respuesta a IPs comprometidas.

### ☁️ Migración a Cloud (AWS)

La migración a una plataforma *Cloud* como AWS se justifica por:

- **Escalabilidad:** Horizontal y vertical en la capacidad de la máquina virtual (almacenamiento, RAM, *cores*).
- **Seguridad Avanzada:** Uso de **IAM**, **VPC** para redes privadas, *Service Control Policies (SCP)*, y **Zero Trust**.
- **Alta Disponibilidad:** Réplicas en diferentes zonas de disponibilidad (*Availability zones*) para SLAs más altos.
- **Personalización:** Libertad para escoger lenguajes y bases de datos, permitiendo migrar hacia nuevas tecnologías.

El plan de migración más sencillo desde InfinityFree a AWS es:
- **Opción #1:** VPC - SG > Amazon Lightsail + DB MySQL dentro de la misma instancia o RDS MySQL pequeña (`db.t3.micro`).

El plan más elaborado y escalable es:
- **Opción #5:** VPC - SG > EKS + Karpenter + Aurora MySQL Serverless v2 + S3 (Integración a los *pods* vía CSI) - Cloudfront + ELB Ingress Controller + Route 53.

**Mejoras de Seguridad en Cloud Provider:**
- Implementación de seguridad mediante **Security Groups (SG)** a nivel de instancia.
- Uso de **Amazon Shield** (habilitado por defecto) para protección contra DDOS.
- Base de datos en segmento de red privado con acceso mediante *bastion host* o *Session Manager*.
- Uso de **Secrets Manager** para credenciales.
- Uso de **Cloudfront con WAF**.

### Preguntas de Reflexión (Cloud)

#### 1. ¿Qué es despliegue y cómo lo hicieron en este proyecto?
Configurar un servidor y exponer puertos para escuchar peticiones entrantes.

#### 2. ¿Qué limitaciones encontraron en InfinityFree?
- No hay balanceo de carga
- La configuración de la base de datos es limitada

#### 3. ¿Qué servicio equivalente usarían en AWS, Azure o GCP?
- **Archivos estáticos**: S3
- **Base de datos**: MariaDB
- **Hosting del sitio**: EC2 instances, con EKS para escalamiento y balanceo de carga

#### 4. ¿Cómo resolverían escalabilidad y alta disponibilidad en la nube?
Usando EKS para tener diferentes nodos y pods que puedan responder a solicitudes.

## 🔗 Enlaces Útiles

- **Sitio web**: https://cursoingles.gt.tc/?i=1
- **Panel admin**: https://cursoingles.gt.tc/admin/login.php
- **Repositorio**: https://github.com/nano871022/talentotech2-easyapp

## 🛠️ Herramientas Utilizadas

- Visual Studio Code
- FileZilla FTP
- MySQL
- PHP
- phpMyAdmin
- draw.io
- GitHub

---

*Proyecto desarrollado como parte del Taller 1 - Arquitectura en la Nube*
