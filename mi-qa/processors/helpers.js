"use strict";

/**
 * Artillery custom processor functions.
 * Referenciado en los tests con:  processor: "../processors/helpers.js"
 *
 * Cada función exportada recibe (requestParams, context, ee, next)
 * y puede modificar headers, body, o variables del contexto.
 */

/**
 * Genera un ID único para correlación de requests.
 */
function generateRequestId(requestParams, context, ee, next) {
  context.vars.requestId =
    "req_" + Date.now() + "_" + Math.random().toString(36).substring(2, 8);
  return next();
}

/**
 * Registra en consola el inicio de un escenario (útil para depuración).
 */
function logScenarioStart(requestParams, context, ee, next) {
  const timestamp = new Date().toISOString();
  console.log(`[${timestamp}] Scenario started — VU: ${context.vars.$uuid}`);
  return next();
}

/**
 * Inyecta un header de correlación en cada request.
 */
function addCorrelationHeader(requestParams, context, ee, next) {
  if (!requestParams.headers) {
    requestParams.headers = {};
  }
  requestParams.headers["X-Correlation-Id"] =
    context.vars.requestId || "no-id";
  return next();
}

/**
 * afterResponse hook — registra latencia y código de estado.
 */
function logResponse(requestParams, response, context, ee, next) {
  const timestamp = new Date().toISOString();
  const status = response.statusCode;
  const url = requestParams.url;
  if (status >= 400) {
    console.warn(`[${timestamp}] ⚠ ${status} — ${url}`);
  }
  return next();
}

module.exports = {
  generateRequestId,
  logScenarioStart,
  addCorrelationHeader,
  logResponse,
};
