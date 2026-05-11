<?php
namespace App\Core;

use App\Exceptions\SystemException;

class ErrorHandler {
    public static function handle(\Throwable $e) {
        $logFile = APP_ROOT . '/error.log';
        $logEntry = "[" . date('Y-m-d H:i:s') . "] " . get_class($e) . ": " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        // Limpiar cualquier salida previa
        if (ob_get_length()) ob_clean();

        $statusCode = ($e instanceof SystemException) ? $e->getStatusCode() : 500;
        $message = $e->getMessage();
        $details = ($e instanceof SystemException) ? $e->getDetails() : [];
        
        // Log the error (In a real app, use a logger)
        error_log("[System Error] $message - Trace: " . $e->getTraceAsString());

        if (isAjax()) {
            self::handleAjaxError($message, $statusCode, $details);
        } else {
            self::handleViewError($message, $statusCode, $e);
        }
    }

    private static function handleAjaxError($message, $statusCode, $details) {
        jsonResponse([
            'error' => true,
            'message' => $message,
            'status' => $statusCode,
            'details' => $details,
            'timestamp' => date('Y-m-d H:i:s')
        ], $statusCode);
    }

    private static function handleViewError($message, $statusCode, $e) {
        if ($e instanceof \App\Exceptions\ValidationException) {
            setFlash('error', $message);
            // Intentar volver a la página anterior
            $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL;
            header('Location: ' . $referer);
            exit;
        }

        http_response_code($statusCode);
        $pageTitle = "Error $statusCode";
        $errorMsg = $message;
        
        // Cargar vista de error premium
        include APP_ROOT . '/views/errors/standard_error.php';
        exit;
    }
}
