<?php

namespace AndyFraussen\Dokapi\Dto;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class ParticipantIdentifier implements PayloadInterface
{
    public readonly string $value;
    public readonly ?string $scheme;

    private function __construct(string $value, ?string $scheme)
    {
        $this->value = $value;
        $this->scheme = $scheme;
    }

    public static function of(string $value, ?string $scheme = null): self
    {
        return new self($value, $scheme);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['value'] ?? ''),
            isset($data['scheme']) ? (string) $data['scheme'] : null
        );
    }

    public function toArray(): array
    {
        $payload = ['value' => $this->value];
        if ($this->scheme !== null) {
            $payload['scheme'] = $this->scheme;
        }
        return $payload;
    }
}
