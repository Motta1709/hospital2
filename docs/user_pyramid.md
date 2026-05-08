# Pirámides de Usuarios Esperados - PharmaCRM / Hospital

Este documento presenta dos modelos de distribución de usuarios para el sistema, fundamentales para el diseño de pruebas de carga y la planificación de infraestructura.

## 1. Pirámide Operativa (Sin Clientes)

Este modelo se centra exclusivamente en el personal interno de la farmacia/hospital. Es ideal para pruebas de carga en el módulo POS e Inventario.

| Rol | Nivel | % Usuarios | Cantidad (Base 5K) | Descripción |
| :--- | :--- | :--- | :--- | :--- |
| **Administrador** | Cúspide | 5% | 250 | Configuración global y seguridad. |
| **Supervisor** | Medio | 15% | 750 | Gestión de inventario y reportes. |
| **Cajero** | Base | 80% | 4,000 | Operaciones POS continuas. |

## 2. Pirámide del Ecosistema (Con Clientes)

Este modelo incluye a los pacientes/clientes que interactúan con el sistema (ej. portal de pacientes, reservas, lealtad). Es ideal para pruebas de estrés general del sistema.

| Rol | Nivel | % Usuarios | Cantidad (Base 5K) | Descripción |
| :--- | :--- | :--- | :--- | :--- |
| **Administrador** | Cúspide | 1% | 50 | Gestión del ecosistema. |
| **Supervisor** | Alto | 4% | 200 | Supervisión operativa. |
| **Cajero** | Medio | 25% | 1,250 | Atención directa al cliente. |
| **Cliente** | Base | 70% | 3,500 | Consultas de historial, citas, puntos. |

## Visualización Comparativa

![Comparación de Pirámides](/docs/images/user_pyramids_comparison.png)
*Izquierda: Pirámide Operativa (Interna) | Derecha: Pirámide del Ecosistema (Total)*
