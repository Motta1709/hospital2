# PharmaCRM - Sistema de Gestión Farmacéutica

**Proyecto:** PharmaCRM
**Plataforma:** Aplicación Web (Optimizado para XAMPP/WAMP)
**Tecnología:** PHP Vanila (Arquitectura MVC)
**Base de Datos:** MySQL (UTF-8 Compatible)

## Descripción del Proyecto

PharmaCRM es una plataforma integral diseñada específicamente para farmacias independientes en Colombia. El sistema combina la gestión de relaciones con el cliente (CRM) con un robusto control de inventarios y punto de venta (POS), permitiendo una operación eficiente, trazable y moderna.

## Características Principales

### 1. Gestión de Inventarios Avanzada (Kardex FIFO/PEPS)
- **Control por Lotes:** Registro detallado de cada entrada de producto incluyendo número de lote, fecha de vencimiento y costo.
- **Modelo FIFO (First-In, First-Out):** Lógica automatizada que prioriza la salida de productos con vencimiento más próximo durante la venta.
- **Semaforización Visual:** Indicadores de estado (Rojo/Amarillo/Verde) para niveles críticos de stock y alertas de vencimiento (30/90 días).
- **Historial de Movimientos:** Auditoría completa de entradas, salidas y ajustes por producto.

### 2. Punto de Venta (POS) y Carrito Multiselección
- **Carrito Flexible:** Permite añadir múltiples productos y seleccionar específicamente cuáles se desean pagar en la transacción actual.
- **Búsqueda Inteligente:** Localización rápida de medicamentos por nombre, principio activo (genérico) o código de barras.
- **Descuentos y Puntos:** Aplicación automática de beneficios del programa de fidelización.

### 3. Pasarela de Pagos Segura (ePayco)
- **Integración Nativa:** Procesamiento de pagos mediante el SDK de ePayco.
- **Modo Sandbox:** Configuración lista para pruebas de transacciones en entorno seguro.
- **Validación en Tiempo Real:** Confirmación automática del estado de pago al finalizar la transacción.

### 4. Programa de Fidelización
- **Sistema de Puntos:** Acumulación automática de puntos por cada compra realizada.
- **Redención de Beneficios:** Los clientes pueden usar sus puntos acumulados como parte de pago.
- **Perfil de Cliente:** Seguimiento de hábitos de compra y estadísticas de fidelidad.

### 5. Seguridad y Roles (RBAC)
- **Control de Acceso:** Roles definidos (Admin, Regente, Vendedor) con permisos específicos por módulo.
- **Autenticación Segura:** Manejo de sesiones y validación de credenciales.

## Stack Tecnológico

### Backend
- **Lenguaje:** PHP 8.x (Arquitectura MVC personalizada)
- **Base de Datos:** MySQL con soporte UTF-8 completo (`utf8mb4_unicode_ci`)
- **Conexión:** PDO (PHP Data Objects) con Singleton Pattern

### Frontend
- **Diseño:** Vanilla CSS3 con estética premium (Glassmorphism, Dark Mode compatible)
- **Iconografía:** Font Awesome 6
- **Interactividad:** JavaScript Vanila (AJAX/Fetch API)
- **Tipografía:** Google Fonts (Inter/Roboto)

## Estructura del Proyecto

```
hospital/
├── app/
│   ├── controllers/    # Lógica de negocio (MVC)
│   ├── models/         # Interacción con base de datos
│   ├── helpers/        # Utilidades (Auth, Validator, etc.)
│   └── ...
├── config/             # Archivos de configuración (DB, App)
├── database/           # Scripts SQL y migraciones
├── public/             # Activos estáticos (CSS, JS, Imágenes)
├── views/              # Capa de presentación (Templates PHP)
├── .env                # Variables de entorno
└── index.php           # Punto de entrada y Router
```

## Configuración y Despliegue

1. **Requisitos:** WAMP/XAMPP con PHP >= 8.0 y MySQL.
2. **Base de Datos:** Importar `database/schema.sql` y `database/kardex_schema.sql` en tu servidor MySQL.
3. **Configuración:** Ajustar las credenciales en `config/database.php` y las llaves de ePayco en `config/app.php`.
4. **Acceso:** Abrir la URL en el navegador (ej: `http://localhost/hospital`).

## Licencia
Este proyecto es propiedad privada de PharmaCRM. Todos los derechos reservados.
