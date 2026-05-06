const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');

// Obtener el archivo de test desde los argumentos
const testFile = process.argv[2];

if (!testFile) {
    console.error('Error: Debes especificar un archivo de prueba. Ej: node runner.js tests/basico.yaml');
    process.exit(1);
}

// Crear nombre de archivo basado en timestamp
const now = new Date();
const timestamp = now.getFullYear() +
    String(now.getMonth() + 1).padStart(2, '0') +
    String(now.getDate()).padStart(2, '0') + "_" +
    String(now.getHours()).padStart(2, '0') +
    String(now.getMinutes()).padStart(2, '0') +
    String(now.getSeconds()).padStart(2, '0');

const testName = path.basename(testFile, '.yaml');
const logFileName = `${testName}_${timestamp}.log`;
const logPath = path.join(__dirname, 'logs', logFileName);

// Asegurar que la carpeta logs exista
if (!fs.existsSync(path.join(__dirname, 'logs'))) {
    fs.mkdirSync(path.join(__dirname, 'logs'));
}

console.log(`\x1b[36m[Artillery Runner]\x1b[0m Iniciando prueba: ${testFile}`);
console.log(`\x1b[36m[Artillery Runner]\x1b[0m El log se guardará en: mi-qa/logs/${logFileName}\n`);

// Ejecutar Artillery
const child = exec(`npx artillery run ${testFile}`);

let logContent = `TEST LOG - ${testFile}\nFECHA: ${now.toLocaleString()}\n------------------------------------------\n`;

child.stdout.on('data', (data) => {
    process.stdout.write(data);
    logContent += data;
});

child.stderr.on('data', (data) => {
    process.stderr.write(data);
    logContent += data;
});

child.on('close', (code) => {
    fs.writeFileSync(logPath, logContent);
    console.log(`\n\x1b[32m[Artillery Runner]\x1b[0m Prueba finalizada (Código: ${code})`);
    console.log(`\x1b[32m[Artillery Runner]\x1b[0m Log guardado exitosamente.`);
});
