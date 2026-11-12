<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class PostalAddress implements PayloadInterface
{
    public function __construct(
        public readonly string $streetName,
        public readonly string $cityName,
        public readonly string $postalZone,
        public readonly string $countrySubentity,
        public readonly string $countryCode,
        public readonly ?string $additionalStreetName = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['streetName'] ?? ''),
            (string) ($data['cityName'] ?? ''),
            (string) ($data['postalZone'] ?? ''),
            (string) ($data['countrySubentity'] ?? ''),
            (string) ($data['countryCode'] ?? ''),
            isset($data['additionalStreetName']) ? (string) $data['additionalStreetName'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), static fn($value) => $value !== null);
    }
}
