<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Enums\MandateStatus;

final class Mandate
{
    /**
     * @param array<string, string> $identifier
     * @param list<PeppolEntity> $peppolEntities
     * @param list<MandateCapability> $capabilities
     * @param list<KycDataHistoryItem> $kycDataHistory
     */
    private function __construct(
        public readonly string $ulid,
        public readonly MandateStatus|string $status,
        public readonly string $countryCode,
        public readonly string $contractType,
        public readonly string $creationTimestamp,
        public readonly string $lastModifiedTimestamp,
        public readonly string $startDate,
        public readonly array $identifier,
        public readonly KycData $kycData,
        public readonly array $peppolEntities,
        public readonly array $capabilities,
        public readonly array $kycDataHistory,
        public readonly ?string $endDate,
        public readonly ?string $statusMessage,
        public readonly ?string $activatedAt,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $status = (string) ($data['status'] ?? '');

        return new self(
            (string) ($data['ulid'] ?? ''),
            MandateStatus::tryFrom($status) ?? $status,
            (string) ($data['countryCode'] ?? ''),
            (string) ($data['contractType'] ?? ''),
            (string) ($data['creationTimestamp'] ?? ''),
            (string) ($data['lastModifiedTimestamp'] ?? ''),
            (string) ($data['startDate'] ?? ''),
            (array) ($data['identifier'] ?? []),
            KycData::fromArray((array) ($data['kycData'] ?? [])),
            array_values(array_map(static fn(array $item) => PeppolEntity::fromArray($item), (array) ($data['peppolEntities'] ?? []))),
            array_values(array_map(static fn(array $item) => MandateCapability::fromArray($item), (array) ($data['capabilities'] ?? []))),
            array_values(array_map(static fn(array $item) => KycDataHistoryItem::fromArray($item), (array) ($data['kycDataHistory'] ?? []))),
            isset($data['endDate']) ? (string) $data['endDate'] : null,
            isset($data['statusMessage']) ? (string) $data['statusMessage'] : null,
            isset($data['activatedAt']) ? (string) $data['activatedAt'] : null,
        );
    }
}
