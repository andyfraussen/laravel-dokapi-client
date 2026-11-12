<?php

namespace AndyFraussen\Dokapi\Dto\BusinessCards;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class BusinessContact implements PayloadInterface
{
    public function __construct(
        public readonly ?string $type = null,
        public readonly ?string $name = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?string $email = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'name' => $this->name,
            'phoneNumber' => $this->phoneNumber,
            'email' => $this->email,
        ], static fn($value) => $value !== null);
    }
}
