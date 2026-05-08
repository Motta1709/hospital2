# 🚀 Suite de Pruebas de Carga - PharmaCRM / Hospital

Este módulo utiliza **Artillery.io** para realizar pruebas de rendimiento, carga y estrés sobre la infraestructura de dckcloud.com.

## 📂 Estructura del Proyecto

- `config/`: Configuraciones base y por entorno (Local, Staging, Prod).
- `data/`: Archivos CSV y JSON con datos de prueba (usuarios, payloads).
- `processors/`: Lógica personalizada en JavaScript (tokens, logging).
- `scripts/`: Automatización en PowerShell para ejecutar tests rápidamente.
- `tests/`: Definición de los escenarios de prueba (.yml).
- `reports/`: Resultados y reportes HTML generados.

## 🛠️ Requisitos

1. Node.js (v18+)
2. Artillery instalado globalmente: `npm install -g artillery`
3. Dependencias locales: `npm install` (desde esta carpeta)

## 🏃 Cómo Ejecutar

### 1. Pruebas Rápidas (Smoke)
Valida que los endpoints básicos respondan:
```powershell
.\scripts\run_smoke.ps1
```

### 2. Prueba de Carga (Load Test)
Simula tráfico de usuarios reales escalando progresivamente:
```powershell
.\scripts\run_load.ps1
```

### 3. Prueba de Estrés (Stress Test)
Encuentra el punto de quiebre (usar con precaución):
```powershell
.\scripts\run_stress.ps1
```

## 📊 Reportes
Después de cada ejecución, se generará un archivo `.html` en la carpeta `reports/`. Ábrelo en cualquier navegador para ver gráficas de latencia, RPS y errores.

---
*Configuración basada en estándares de dckcloud.com*
