<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class KycCompany implements PayloadInterface
{
    public function __construct(
        public readonly string $name,
        public readonly PostalAddress $address,
        public readonly string $contactEmail,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['name'] ?? ''),
            PostalAddress::fromArray((array) ($data['address'] ?? [])),
            (string) ($data['contactEmail'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return ['name' => $this->name, 'address' => $this->address->toArray(), 'contactEmail' => $this->contactEmail];
    }
}
