# Manual Técnico - PharmaCRM

## 1. Arquitectura del Sistema
PharmaCRM está construido bajo el patrón **MVC (Modelo-Vista-Controlador)** utilizando tecnologías nativas para garantizar el máximo rendimiento en entornos locales (XAMPP).

- **Lenguaje**: PHP 8.x
- **Base de Datos**: MySQL / MariaDB (Normalización 3NF)
- **Frontend**: HTML5, CSS3 (Vanilla), JavaScript (ES6+)
- **Iconografía**: FontAwesome 6

## 2. Estructura de Directorios
- `/app`: Núcleo de la aplicación.
    - `/controllers`: Lógica de negocio.
    - `/models`: Interacción con la base de datos.
    - `/helpers`: Funciones utilitarias.
- `/config`: Archivos de configuración (DB, App).
- `/database`: Scripts SQL y semillas de datos.
- `/public`: Archivos estáticos (CSS, JS, Imágenes).
- `/views`: Plantillas de la interfaz de usuario.

## 3. Requisitos de Instalación
1. Servidor local: **XAMPP**, WAMP o Laragon.
2. PHP >= 7.4.
3. MySQL >= 5.7.
4. Extensión PDO de PHP habilitada.

## 4. Configuración de Base de Datos
El script de creación se encuentra en `database/schema.sql`.
El sistema utiliza una base de datos llamada `pharmacrm`.

### Variables de Entorno (.env)
```env
DB_HOST=localhost
DB_NAME=pharmacrm
DB_USER=root
DB_PASS=
```

## 5. Seguridad
- Las contraseñas están encriptadas mediante `password_hash()`.
- Sesiones protegidas para control de acceso por roles.
- Prevención de Inyección SQL mediante el uso de Sentencias Preparadas (PDO).

## 6. Mantenimiento
- Se recomienda realizar backups periódicos de la base de datos.
- Los logs de auditoría se pueden consultar en la tabla `audit_log`.
