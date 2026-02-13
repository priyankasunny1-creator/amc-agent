<?php

/**
 * ApiException standardizes operational errors with HTTP status and safe metadata.
 */
class ApiException extends Exception
{
    private int $httpStatus;
    private array $details;

    public function __construct(string $message, int $httpStatus = 400, array $details = [], ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->httpStatus = $httpStatus;
        $this->details = $details;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
?>