# Simula tráfico real escalado.
Write-Host "Iniciando Load Test en Staging..." -ForegroundColor Cyan
artillery run --environment staging tests/carga.yml --output reports/load-report.json
artillery report reports/load-report.json
Write-Host "Reporte generado en reports/load-report.json.html" -ForegroundColor Green
