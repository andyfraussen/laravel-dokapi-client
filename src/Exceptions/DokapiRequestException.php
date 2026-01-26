<?php

namespace AndyFraussen\Dokapi\Exceptions;

class DokapiRequestException extends DokapiException
{
    protected int $statusCode;
    protected string $responseBody;

    public function __construct(string $message, int $statusCode, string $responseBody, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
