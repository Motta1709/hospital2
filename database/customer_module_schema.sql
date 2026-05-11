SET NAMES utf8mb4;
-- =====================================================
-- PharmaCRM - Modulo Cliente (Dashboard Usuario)
-- Tablas desacopladas del core administrativo
-- Normalización: 3FN | ISO 9001 Trazabilidad
-- =====================================================

USE pharmacrm;

-- =====================================================
-- TABLA: client_addresses (direcciones multiples)
-- RF-01: Gestión de perfil - direcciones con etiquetas
-- =====================================================
CREATE TABLE IF NOT EXISTS client_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    label ENUM('casa', 'trabajo', 'otro') DEFAULT 'casa',
    address_line TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    neighborhood VARCHAR(150),
    postal_code VARCHAR(10),
    instructions TEXT,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    is_default TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    INDEX idx_client (client_id),
    INDEX idx_active (is_active)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pqrsf (Peticiones, Quejas, Reclamos, etc.)
-- RF-02: Modulo PQRSF - radicado unico, auditable
-- =====================================================
CREATE TABLE IF NOT EXISTS pqrsf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    radicado VARCHAR(30) NOT NULL UNIQUE,
    tipo ENUM(
        'peticion',
        'queja',
        'reclamo',
        'sugerencia',
        'felicitacion'
    ) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM(
        'abierto',
        'en_proceso',
        'cerrado',
        'rechazado'
    ) DEFAULT 'abierto',
    respuesta TEXT DEFAULT NULL,
    respondido_por INT DEFAULT NULL,
    fecha_respuesta DATETIME DEFAULT NULL,
    fecha_limite DATE DEFAULT NULL,
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    FOREIGN KEY (respondido_por) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_client (client_id),
    INDEX idx_estado (estado),
    INDEX idx_radicado (radicado),
    INDEX idx_fecha (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: devoluciones (solicitudes de devolucion)
-- RF-04: Solo sobre compras en estado Entregado
-- =====================================================
CREATE TABLE IF NOT EXISTS devoluciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    sale_id INT NOT NULL,
    sale_item_id INT NOT NULL,
    motivo ENUM(
        'defectuoso',
        'error_pedido',
        'no_satisface',
        'caducado',
        'otro'
    ) NOT NULL,
    descripcion TEXT,
    evidencia_path VARCHAR(255) DEFAULT NULL,
    estado ENUM(
        'solicitada',
        'en_revision',
        'aprobada',
        'rechazada'
    ) DEFAULT 'solicitada',
    observacion_admin TEXT DEFAULT NULL,
    revisado_por INT DEFAULT NULL,
    fecha_revision DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    FOREIGN KEY (sale_id) REFERENCES sales (id) ON DELETE CASCADE,
    FOREIGN KEY (sale_item_id) REFERENCES sale_items (id) ON DELETE CASCADE,
    FOREIGN KEY (revisado_por) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_client (client_id),
    INDEX idx_sale (sale_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: domicilios (pedidos a domicilio)
-- RF-05: Entrega inmediata/programada + zona cobertura
-- =====================================================
CREATE TABLE IF NOT EXISTS domicilios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    sale_id INT DEFAULT NULL,
    address_id INT NOT NULL,
    tipo_entrega ENUM('inmediata', 'programada') DEFAULT 'inmediata',
    fecha_programada DATE DEFAULT NULL,
    franja_horaria ENUM('manana', 'tarde', 'noche') DEFAULT NULL,
    estado ENUM(
        'pendiente',
        'confirmado',
        'despachado',
        'entregado',
        'cancelado'
    ) DEFAULT 'pendiente',
    notas TEXT,
    costo_envio DECIMAL(12, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    FOREIGN KEY (sale_id) REFERENCES sales (id) ON DELETE SET NULL,
    FOREIGN KEY (address_id) REFERENCES client_addresses (id) ON DELETE CASCADE,
    INDEX idx_client (client_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: reservas (reservas de medicamentos)
-- RF-06: Medicamentos proximos a llegar
-- =====================================================
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    product_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    estado ENUM(
        'pendiente',
        'aprobada',
        'rechazada',
        'entregada',
        'cancelada'
    ) DEFAULT 'pendiente',
    observacion_admin TEXT DEFAULT NULL,
    aprobado_por INT DEFAULT NULL,
    fecha_aprobacion DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    FOREIGN KEY (aprobado_por) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_client (client_id),
    INDEX idx_product (product_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: prescripciones (archivos de prescripcion)
-- RNF-07: Validación de conformidad legal
-- =====================================================
CREATE TABLE IF NOT EXISTS prescripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    archivo_path VARCHAR(255) NOT NULL,
    archivo_nombre VARCHAR(255) NOT NULL,
    validado TINYINT(1) DEFAULT 0,
    validado_por INT DEFAULT NULL,
    fecha_validacion DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas (id) ON DELETE CASCADE,
    FOREIGN KEY (validado_por) REFERENCES users (id) ON DELETE SET NULL,
    INDEX idx_reserva (reserva_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: notificaciones (registro de envios)
-- RF-07: Trazabilidad de correos SMTP
-- =====================================================
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    evento ENUM(
        'compra_confirmada',
        'reserva_estado',
        'domicilio_estado',
        'pqrsf_respuesta',
        'devolucion_estado',
        'sistema'
    ) NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    estado_envio ENUM(
        'enviado',
        'fallido',
        'pendiente'
    ) DEFAULT 'pendiente',
    leida TINYINT(1) DEFAULT 0,
    referencia_tipo VARCHAR(50) DEFAULT NULL,
    referencia_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    INDEX idx_client (client_id),
    INDEX idx_evento (evento),
    INDEX idx_leida (leida),
    INDEX idx_fecha (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: client_audit_log (bitacora ISO 9001)
-- RNF-06: Trazabilidad y auditoría
-- =====================================================
CREATE TABLE IF NOT EXISTS client_audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    campo_alterado VARCHAR(100) DEFAULT NULL,
    valor_anterior TEXT DEFAULT NULL,
    valor_nuevo TEXT DEFAULT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients (id) ON DELETE CASCADE,
    INDEX idx_client (client_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- =====================================================
-- DATOS DE PRUEBA - Módulo Cliente
-- =====================================================

-- Direcciones de ejemplo
INSERT INTO
    client_addresses (
        client_id,
        label,
        address_line,
        city,
        neighborhood,
        is_default
    )
VALUES (
        1,
        'casa',
        'Calle 15 #8-32, Barrio El Centro',
        'Florencia',
        'El Centro',
        1
    ),
    (
        1,
        'trabajo',
        'Carrera 12 #20-15, Oficina 201',
        'Florencia',
        'La Vega',
        0
    ),
    (
        2,
        'casa',
        'Avenida Circunvalar #5-90',
        'Florencia',
        'Torasso',
        1
    ),
    (
        4,
        'casa',
        'Calle 8 #14-55, Barrio Porvenir',
        'Florencia',
        'El Porvenir',
        1
    );

-- PQRSF de ejemplo
INSERT INTO
    pqrsf (
        client_id,
        radicado,
        tipo,
        asunto,
        descripcion,
        estado,
        prioridad
    )
VALUES (
        1,
        'PQRSF-2026-0001',
        'sugerencia',
        'Horario extendido fines de semana',
        'Seria muy util que la farmacia abriera hasta las 8pm los sabados para quienes trabajamos.',
        'abierto',
        'baja'
    ),
    (
        2,
        'PQRSF-2026-0002',
        'queja',
        'Demora en entrega a domicilio',
        'Mi pedido DOM-2026-003 tardo mas de 3 horas en llegar. El estimado era de 45 minutos.',
        'en_proceso',
        'alta'
    ),
    (
        1,
        'PQRSF-2026-0003',
        'felicitacion',
        'Excelente atencion del personal',
        'Quiero felicitar al cajero Carlos por su amabilidad y paciencia al explicarme mis medicamentos.',
        'cerrado',
        'baja'
    ),
    (
        3,
        'PQRSF-2026-0004',
        'reclamo',
        'Producto vencido en estante',
        'Encontre un producto con fecha de vencimiento pasada en la estanteria de vitaminas.',
        'abierto',
        'alta'
    );

-- Reservas de ejemplo
INSERT INTO
    reservas (
        client_id,
        product_id,
        cantidad,
        estado
    )
VALUES (1, 4, 2, 'pendiente'),
    (2, 10, 1, 'aprobada'),
    (4, 7, 3, 'entregada');

-- Domicilios de ejemplo
INSERT INTO
    domicilios (
        client_id,
        sale_id,
        address_id,
        tipo_entrega,
        estado,
        costo_envio
    )
VALUES (
        1,
        1,
        1,
        'inmediata',
        'entregado',
        5000.00
    ),
    (
        2,
        2,
        3,
        'programada',
        'despachado',
        5000.00
    );

-- Notificaciones de ejemplo
INSERT INTO
    notificaciones (
        client_id,
        evento,
        titulo,
        mensaje,
        estado_envio,
        leida,
        referencia_tipo,
        referencia_id
    )
VALUES (
        1,
        'compra_confirmada',
        'Compra confirmada',
        'Tu compra FV-2026-0001 por $31.000 ha sido procesada exitosamente.',
        'enviado',
        1,
        'sales',
        1
    ),
    (
        1,
        'pqrsf_respuesta',
        'PQRSF respondida',
        'Tu felicitacion PQRSF-2026-0003 ha sido recibida y cerrada. Gracias por tus comentarios.',
        'enviado',
        0,
        'pqrsf',
        3
    ),
    (
        2,
        'reserva_estado',
        'Reserva aprobada',
        'Tu reserva de Glibenclamida Genfar ha sido aprobada. Puedes recogerla en sucursal.',
        'enviado',
        0,
        'reservas',
        2
    ),
    (
        2,
        'domicilio_estado',
        'Pedido despachado',
        'Tu pedido a domicilio ha sido despachado. Llegara pronto a tu direccion.',
        'enviado',
        0,
        'domicilios',
        2
    ),
    (
        4,
        'compra_confirmada',
        'Compra confirmada',
        'Tu compra FV-2026-0003 por $89.000 ha sido procesada exitosamente.',
        'enviado',
        1,
        'sales',
        3
    );