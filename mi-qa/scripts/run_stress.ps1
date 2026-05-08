# Busca el punto de quiebre del sistema.
Write-Host " ADVERTENCIA: Iniciando prueba de ESTRÉS EXTREMO..." -ForegroundColor Yellow
artillery run --environment production tests/avanzado.yml --output reports/stress-report.json
artillery report reports/stress-report.json
Write-Host "Análisis completado. Reporte en reports/stress-report.json.html" -ForegroundColor Red
