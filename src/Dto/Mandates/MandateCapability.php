<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Enums\MandateCapabilityScope;
use AndyFraussen\Dokapi\Enums\MandateCapabilityStatus;

final class MandateCapability
{
    private function __construct(
        public readonly string $ulid,
        public readonly ParticipantIdentifier $participantIdentifier,
        public readonly MandateCapabilityScope|string $scope,
        public readonly MandateCapabilityStatus|string $status,
        public readonly string $startDate,
        public readonly ?string $statusMessage,
        public readonly ?string $endDate,
        public readonly ?string $activatedAt,
        public readonly ?string $mandateUlid,
        public readonly ?string $clientUlid,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $scope = (string) ($data['scope'] ?? '');
        $status = (string) ($data['status'] ?? '');

        return new self(
            (string) ($data['ulid'] ?? ''),
            ParticipantIdentifier::fromArray((array) ($data['participantIdentifier'] ?? [])),
            MandateCapabilityScope::tryFrom($scope) ?? $scope,
            MandateCapabilityStatus::tryFrom($status) ?? $status,
            (string) ($data['startDate'] ?? ''),
            isset($data['statusMessage']) ? (string) $data['statusMessage'] : null,
            isset($data['endDate']) ? (string) $data['endDate'] : null,
            isset($data['activatedAt']) ? (string) $data['activatedAt'] : null,
            isset($data['mandateUlid']) ? (string) $data['mandateUlid'] : null,
            isset($data['clientUlid']) ? (string) $data['clientUlid'] : null,
        );
    }

    public function isActive(): bool
    {
        return $this->status === MandateCapabilityStatus::Active;
    }
}
