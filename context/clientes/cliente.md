Ahora procedo a construir el documento de requerimientos mejorado con el visual del diagrama de arquitectura.
  ::view-transition-group(*),
  ::view-transition-old(*),
  ::view-transition-new(*) {
    animation-duration: 0.25s;
    animation-timing-function: cubic-bezier(0.19, 1, 0.22, 1);
  }
VvisualizeVvisualize show_widgetAquí está el documento de requerimientos revisado, reestructurado y alineado con ISO 9001 e ISO/IEC 25010.

Requerimientos funcionales (RF) — versión revisada
RF-01 · Gestión de perfil
El usuario debe poder visualizar y actualizar sus datos personales (nombre, correo, teléfono), gestionar múltiples direcciones de entrega con etiquetas (casa, trabajo, otro), cambiar su contraseña mediante flujo de verificación por correo y configurar preferencias de comunicación. El sistema debe registrar en bitácora cada modificación con fecha, hora y campo alterado, conforme al principio de trazabilidad de la ISO 9001.
RF-02 · Módulo PQRSF
El sistema debe permitir al usuario crear solicitudes de tipo Petición, Queja, Reclamo, Sugerencia o Felicitación. Cada solicitud debe generar un número de radicado único, inmutable y auditable. El usuario debe poder consultar el historial de sus solicitudes con el estado actualizado: Abierto, En proceso, Cerrado o Rechazado. El sistema debe garantizar que ninguna solicitud quede sin respuesta dentro de los plazos configurados por el administrador, en cumplimiento del ciclo de mejora continua de la ISO 9001.
RF-03 · Gestión de compras
El sistema debe proveer un catálogo de productos con búsqueda y filtros, un carrito de compras persistente durante la sesión, integración con pasarela de pagos y visualización del historial de pedidos con estado detallado por ítem. Toda transacción debe quedar registrada con marca de tiempo y usuario responsable.
RF-04 · Solicitud de devoluciones
El usuario debe poder iniciar una solicitud de devolución únicamente sobre compras en estado Entregado. El sistema debe requerir la selección del ítem a devolver, el motivo (de un catálogo cerrado de opciones) y, opcionalmente, evidencia fotográfica. El estado de la devolución debe ser visible en tiempo real: Solicitada, En revisión, Aprobada, Rechazada.
RF-05 · Pedidos a domicilio
El usuario debe poder seleccionar entre entrega inmediata o entrega programada con fecha y franja horaria. El sistema debe validar que la dirección de entrega esté dentro de la zona de cobertura antes de confirmar el pedido. El usuario debe recibir notificaciones por correo en los hitos: confirmación del pedido, despacho y entrega.
RF-06 · Reservas de medicamentos
El usuario debe poder reservar medicamentos marcados como "próximos a llegar" en el inventario. Si el medicamento requiere prescripción médica (validación por categoría terapéutica), el sistema debe solicitar la carga del documento antes de enviar la reserva. La solicitud debe llegar al administrador con estado Pendiente; este podrá Aprobar o Rechazar con observación obligatoria. El usuario debe ser notificado del resultado por correo electrónico.
RF-07 · Notificaciones por correo (SMTP Gmail)
El sistema debe enviar correos automáticos en los siguientes eventos: confirmación de compra, cambio de estado de reserva, cambio de estado de domicilio y respuesta a PQRSF. Las plantillas deben ser configurables desde el panel administrativo. Cada envío debe quedar registrado en la tabla Notificaciones con estado Enviado, Fallido o Pendiente de reintento.

Requerimientos no funcionales (RNF) — versión revisada
RNF-01 · Arquitectura MVC. El sistema debe implementar el patrón Modelo-Vista-Controlador sin acoplamiento entre capas. Los controladores no deben contener lógica de negocio directa; esta debe residir en clases de servicio dentro de la capa Modelo. Las vistas no deben acceder directamente a la base de datos bajo ninguna circunstancia.
RNF-02 · Normalización de base de datos (3FN). El módulo de usuario debe estar en su propio esquema o base de datos lógica, desacoplado del core administrativo. Todas las tablas deben cumplir la tercera forma normal: sin dependencias transitivas, claves foráneas explícitas e índices sobre columnas de búsqueda frecuente (id_user, estado, fecha). La ausencia de caché hace obligatorio que cada consulta use los índices correctos para mantener tiempos de respuesta aceptables a escala.
RNF-03 · Modularidad. El módulo de usuario debe poder desplegarse, probarse y actualizarse de forma independiente sin afectar el módulo administrativo u otros módulos del sistema. Las interfaces entre módulos deben estar definidas por contratos de API internos documentados.
RNF-04 · Rendimiento sin caché. Al no implementarse caché en ninguna capa, el sistema debe garantizar tiempos de respuesta menores a 2 segundos para el dashboard con hasta 10.000 registros por usuario, mediante consultas optimizadas, paginación obligatoria en listados y uso de vistas indexadas para los resúmenes del panel.
RNF-05 · Seguridad de comunicaciones SMTP. Las credenciales de Gmail deben gestionarse mediante variables de entorno cifradas o un gestor de secretos (no en texto plano en el código fuente). La autenticación debe usar OAuth 2.0 o App Passwords con doble factor habilitado. Las credenciales deben rotarse cada 90 días y el acceso a la configuración SMTP debe requerir rol de administrador.
RNF-06 · Trazabilidad y auditoría (ISO 9001). Toda acción del usuario que modifique datos (perfil, reserva, PQRSF, devolución) debe registrarse en una tabla de auditoría con: identificador de usuario, acción realizada, valor anterior, valor nuevo, fecha y hora. Esta bitácora no debe ser modificable ni eliminable por el usuario.
RNF-07 · Validación de conformidad legal (ISO/IEC 25010 — seguridad). El sistema debe mantener un catálogo de medicamentos que requieren prescripción. Cualquier intento de reservar uno de estos medicamentos sin documento adjunto debe ser bloqueado en el controlador, no solo en la vista. La validación debe ejecutarse en backend independientemente de lo que llegue desde el cliente.
RNF-08 · Mantenibilidad. El código debe seguir estándares de documentación interna (comentarios en funciones públicas, contratos de métodos). Cada módulo debe tener pruebas unitarias con cobertura mínima del 70% en la capa de controladores y servicios, en cumplimiento del principio de calidad de software de la ISO/IEC 25010.

Ajustes respecto a la versión anterior
Tres cambios estructurales relevantes se incorporaron en esta revisión. Primero, se añadió la tabla Notificaciones al modelo de datos para garantizar trazabilidad de cada envío SMTP, lo que antes quedaba implícito sin respaldo en base de datos. Segundo, se separó la validación de prescripciones médicas como un requerimiento no funcional explícito (RNF-07) dado que tiene implicaciones legales y de seguridad que van más allá del flujo de negocio. Tercero, se formalizó la bitácora de auditoría (RNF-06) como requerimiento independiente, alineado directamente con el enfoque de mejora continua y control de registros exigido por la ISO 9001, que en la versión anterior solo se mencionaba de forma transversal dentro del módulo PQRSF.