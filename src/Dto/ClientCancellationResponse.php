<?php

namespace AndyFraussen\Dokapi\Dto;

final class ClientCancellationResponse
{
    public readonly string $message;

    private function __construct(string $message)
    {
        $this->message = $message;
    }

    public static function fromArray(array $data): self
    {
        return new self((string) ($data['message'] ?? ''));
    }
}
