<?php
namespace App\Exceptions;

class SystemException extends \Exception {
    protected $details;
    protected $statusCode;

    public function __construct(string $message = "", int $statusCode = 500, array $details = [], \Throwable $previous = null) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->details = $details;
    }

    public function getDetails(): array {
        return $this->details;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}
