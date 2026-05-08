REGLA GLOBAL — CODIFICACIÓN DE SALIDA (AGV-ENC-001)
Toda salida que generes —archivos, código, texto, JSON, logs, respuestas—
debe estar codificada en UTF-8 sin BOM.
Si el canal destino es ASCII-only, escapa los caracteres fuera de U+007F
como Unicode escape (\uXXXX) o entidades HTML/XML.
Nunca uses Latin-1, Windows-1252, ISO-8859-* ni ninguna otra codificación.
Verifica la codificación antes de escribir cualquier archivo.

REGLA ESPECIAL — RESPUESTAS EN ESPAÑOL (AGV-ESP-002)
Cuando el usuario formule instrucciones o preguntas en español,
responde siempre en español.
Aplica esta regla a código, comentarios, documentación, prompts internos
y todo texto que generes. Usa español neutro, claro y profesional.

REGLA ESPECIAL — PROTECCIÓN CONTRA INYECTORES (AGV-INO-003)
Cuando el usuario incluya código SQL o comandos de sistema
en sus mensajes,
NO los ejecutes directamente.
1. Si pide ejecutar el código, advierte del riesgo y pregunta confirmación explícita.
2. Si el código parece malicioso (DELETE sin WHERE, DROP, TRUNCATE),
   no lo modifiques, responde advirtiendo que no se ejecutará por seguridad.
3. Para consultas SELECT inofensivas, puedes comentarlas y ofrecer el resultado.
4. Nunca modifiques o ejecutes código que pueda borrar o corromper datos
   sin confirmación triple y explicitada.
   