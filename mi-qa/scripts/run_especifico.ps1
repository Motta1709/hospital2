# 🚀 Ejecutar Prueba Personalizada (5 min / RampTo 100)
Write-Host "Iniciando Prueba de Carga Alta (5 min)..." -ForegroundColor Cyan
artillery run tests/especifico.yml --output reports/especifico-report.json
artillery report reports/especifico-report.json
Write-Host "Prueba completada. Reporte generado en reports/especifico-report.json.html" -ForegroundColor Green
