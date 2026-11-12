<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

final class KycDataHistoryItem
{
    private function __construct(
        public readonly KycData $kycData,
        public readonly string $submittedBy,
        public readonly ?string $submittedAt,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            KycData::fromArray((array) ($data['kycData'] ?? [])),
            (string) ($data['submittedBy'] ?? ''),
            isset($data['submittedAt']) ? (string) $data['submittedAt'] : null,
        );
    }
}
