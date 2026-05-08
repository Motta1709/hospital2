# Valida rápidamente que el sitio esté arriba.
Write-Host "Iniciando Smoke Test..." -ForegroundColor Cyan
artillery run tests/basico.yml --output reports/smoke-report.json
artillery report reports/smoke-report.json
Write-Host "Reporte generado en reports/smoke-report.json.html" -ForegroundColor Green
