<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class PersonalInformation implements PayloadInterface
{
    public function __construct(public readonly string $firstName, public readonly string $lastName)
    {
    }

    public static function fromArray(array $data): self
    {
        return new self((string) ($data['firstName'] ?? ''), (string) ($data['lastName'] ?? ''));
    }

    public function toArray(): array
    {
        return ['firstName' => $this->firstName, 'lastName' => $this->lastName];
    }
}
