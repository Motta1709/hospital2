<?php
namespace App\Exceptions;

class ValidationException extends SystemException {
    public function __construct(array $errors = []) {
        parent::__construct("Error de validación según normativa vigente.", 422, $errors);
    }
}
