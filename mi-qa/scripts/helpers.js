// Funciones reutilizables para Artillery

module.exports.generarEmail = (context, events, done) => {
    const ts = Date.now();
    context.vars['email'] = `qa-test-${ts}@temporal.com`;
    context.vars['nombre'] = `Test User ${ts}`;
    done();
};

module.exports.generarFechas = (context, events, done) => {
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    
    context.vars['fecha_hoy'] = today.toISOString().split('T')[0];
    context.vars['fecha_ayer'] = yesterday.toISOString().split('T')[0];
    done();
};
