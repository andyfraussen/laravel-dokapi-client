<?php

namespace AndyFraussen\Dokapi\Dto;

final class RegisterDocumentTypeResponse
{
    public readonly string $message;
    public readonly array $payload;

    private function __construct(string $message, array $payload)
    {
        $this->message = $message;
        $this->payload = $payload;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['message'] ?? ''),
            (array) ($data['payload'] ?? [])
        );
    }
}
