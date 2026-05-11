<?php
/**
 * PharmaCRM - Helper de Validación
 */
class Validator {

    private $errors = [];
    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function required($field, $label = null) {
        $label = $label ?? $field;
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->errors[$field] = "El campo {$label} es obligatorio.";
        }
        return $this;
    }

    public function email($field, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "El campo {$label} debe ser un email válido.";
        }
        return $this;
    }

    public function numeric($field, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "El campo {$label} debe ser numérico.";
        }
        return $this;
    }

    public function min($field, $min, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = "El campo {$label} debe tener al menos {$min} caracteres.";
        }
        return $this;
    }

    public function max($field, $max, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = "El campo {$label} no puede exceder {$max} caracteres.";
        }
        return $this;
    }

    public function date($field, $label = null) {
        $label = $label ?? $field;
        if (isset($this->data[$field]) && !strtotime($this->data[$field])) {
            $this->errors[$field] = "El campo {$label} debe tener un formato de fecha válido.";
        }
        return $this;
    }

    public function validate() {
        if ($this->fails()) {
            throw new \App\Exceptions\ValidationException($this->errors);
        }
        return $this->data;
    }

    public function fails() {
        return !empty($this->errors);
    }

    public function errors() {
        return $this->errors;
    }

    public function firstError() {
        return reset($this->errors);
    }
}