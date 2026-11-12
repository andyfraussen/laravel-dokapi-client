<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class KycRepresentative implements PayloadInterface
{
    public function __construct(
        public readonly PersonalInformation $personalInformation,
        public readonly string $dateOfBirth,
        public readonly string $email,
        public readonly string $role,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            PersonalInformation::fromArray((array) ($data['personalInformation'] ?? [])),
            (string) ($data['dateOfBirth'] ?? ''),
            (string) ($data['email'] ?? ''),
            (string) ($data['role'] ?? ''),
        );
    }

    public function toArray(): array
    {
        return [
            'personalInformation' => $this->personalInformation->toArray(),
            'dateOfBirth' => $this->dateOfBirth,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
