<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Enums\MandateStatus;

final class MandateStatusInformation
{
    /**
     * @param array<string, string> $identifier
     * @param list<PeppolEntity> $peppolEntities
     */
    private function __construct(
        public readonly string $ulid,
        public readonly string $clientUlid,
        public readonly MandateStatus|string $status,
        public readonly string $statusMessage,
        public readonly array $identifier,
        public readonly array $peppolEntities,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $status = (string) ($data['status'] ?? '');

        return new self(
            (string) ($data['ulid'] ?? ''),
            (string) ($data['clientUlid'] ?? ''),
            MandateStatus::tryFrom($status) ?? $status,
            (string) ($data['statusMessage'] ?? ''),
            (array) ($data['identifier'] ?? []),
            array_values(array_map(static fn(array $item) => PeppolEntity::fromArray($item), (array) ($data['peppolEntities'] ?? []))),
        );
    }
}
