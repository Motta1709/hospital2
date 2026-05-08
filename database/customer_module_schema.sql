-- =====================================================
-- PharmaCRM - Modulo Cliente (Dashboard Usuario)
-- Tablas desacopladas del core administrativo
-- Normalización: 3FN | ISO 9001 Trazabilidad
-- =====================================================

USE pharmacrm;

-- =====================================================
-- TABLA: direcciones_cliente (direcciones multiples)
-- RF-01: Gestión de perfil - direcciones con etiquetas
-- =====================================================
CREATE TABLE IF NOT EXISTS direcciones_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    etiqueta ENUM('casa', 'trabajo', 'otro') DEFAULT 'casa',
    direccion TEXT NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    barrio VARCHAR(150),
    codigo_postal VARCHAR(10),
    instrucciones TEXT,
    latitud DECIMAL(10,8) DEFAULT NULL,
    longitud DECIMAL(11,8) DEFAULT NULL,
    es_principal TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: pqrsf (Peticiones, Quejas, Reclamos, etc.)
-- RF-02: Modulo PQRSF - radicado unico, auditable
-- =====================================================
CREATE TABLE IF NOT EXISTS pqrsf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    radicado VARCHAR(30) NOT NULL UNIQUE,
    tipo ENUM('peticion', 'queja', 'reclamo', 'sugerencia', 'felicitacion') NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('abierto', 'en_proceso', 'cerrado', 'rechazado') DEFAULT 'abierto',
    respuesta TEXT DEFAULT NULL,
    respondido_por INT DEFAULT NULL,
    fecha_respuesta DATETIME DEFAULT NULL,
    fecha_limite DATE DEFAULT NULL,
    prioridad ENUM('baja', 'media', 'alta') DEFAULT 'media',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (respondido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado),
    INDEX idx_radicado (radicado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: devoluciones (solicitudes de devolucion)
-- RF-04: Solo sobre compras en estado Entregado
-- =====================================================
CREATE TABLE IF NOT EXISTS devoluciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    venta_id INT NOT NULL,
    item_venta_id INT NOT NULL,
    motivo ENUM('defectuoso', 'error_pedido', 'no_satisface', 'caducado', 'otro') NOT NULL,
    descripcion TEXT,
    evidencia_path VARCHAR(255) DEFAULT NULL,
    estado ENUM('solicitada', 'en_revision', 'aprobada', 'rechazada') DEFAULT 'solicitada',
    observacion_admin TEXT DEFAULT NULL,
    revisado_por INT DEFAULT NULL,
    fecha_revision DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
    FOREIGN KEY (item_venta_id) REFERENCES items_venta(id) ON DELETE CASCADE,
    FOREIGN KEY (revisado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_venta (venta_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: domicilios (pedidos a domicilio)
-- RF-05: Entrega inmediata/programada
-- =====================================================
CREATE TABLE IF NOT EXISTS domicilios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    venta_id INT DEFAULT NULL,
    direccion_id INT NOT NULL,
    tipo_entrega ENUM('inmediata', 'programada') DEFAULT 'inmediata',
    fecha_programada DATE DEFAULT NULL,
    franja_horaria ENUM('manana', 'tarde', 'noche') DEFAULT NULL,
    estado ENUM('pendiente', 'confirmado', 'despachado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    notas TEXT,
    costo_envio DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE SET NULL,
    FOREIGN KEY (direccion_id) REFERENCES direcciones_cliente(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: reservas (reservas de medicamentos)
-- RF-06: Medicamentos proximos a llegar
-- =====================================================
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    estado ENUM('pendiente', 'aprobada', 'rechazada', 'entregada', 'cancelada') DEFAULT 'pendiente',
    observacion_admin TEXT DEFAULT NULL,
    aprobado_por INT DEFAULT NULL,
    fecha_aprobacion DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (aprobado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_producto (producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
    FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
    FOREIGN KEY (validado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_reserva (reserva_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: notificaciones (registro de envios)
-- RF-07: Trazabilidad de correos SMTP
-- =====================================================
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    evento ENUM('compra_confirmada', 'reserva_estado', 'domicilio_estado', 'pqrsf_respuesta', 'devolucion_estado', 'sistema') NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    estado_envio ENUM('enviado', 'fallido', 'pendiente') DEFAULT 'pendiente',
    leida TINYINT(1) DEFAULT 0,
    referencia_tipo VARCHAR(50) DEFAULT NULL,
    referencia_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_leida (leida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: bitacora_cliente (bitacora ISO 9001)
-- RNF-06: Trazabilidad y auditoría
-- =====================================================
CREATE TABLE IF NOT EXISTS bitacora_cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    campo_alterado VARCHAR(100) DEFAULT NULL,
    valor_anterior TEXT DEFAULT NULL,
    valor_nuevo TEXT DEFAULT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
