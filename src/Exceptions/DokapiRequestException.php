<?php

namespace AndyFraussen\Dokapi\Exceptions;

use AndyFraussen\Dokapi\Dto\ProblemDetail;

class DokapiRequestException extends DokapiException
{
    protected int $statusCode;
    protected string $responseBody;
    protected ?ProblemDetail $problemDetail;

    public function __construct(string $message, int $statusCode, string $responseBody, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
        $this->problemDetail = $this->parseProblemDetail($responseBody);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    public function getProblemDetail(): ?ProblemDetail
    {
        return $this->problemDetail;
    }

    private function parseProblemDetail(string $body): ?ProblemDetail
    {
        if ($body === '') {
            return null;
        }

        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!is_array($decoded) || !ProblemDetail::isProblemDetail($decoded)) {
            return null;
        }

        return ProblemDetail::fromArray($decoded);
    }
}
