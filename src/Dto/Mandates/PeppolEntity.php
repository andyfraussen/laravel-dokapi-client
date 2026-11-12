<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class PeppolEntity implements PayloadInterface
{
    public function __construct(
        public readonly ParticipantIdentifier $participantIdentifier,
        public readonly ?bool $sendingCapabilities = null,
        public readonly ?bool $receivingCapabilities = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            ParticipantIdentifier::fromArray((array) ($data['participantIdentifier'] ?? [])),
            isset($data['sendingCapabilities']) ? (bool) $data['sendingCapabilities'] : null,
            isset($data['receivingCapabilities']) ? (bool) $data['receivingCapabilities'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'participantIdentifier' => $this->participantIdentifier->toArray(),
            'sendingCapabilities' => $this->sendingCapabilities,
            'receivingCapabilities' => $this->receivingCapabilities,
        ], static fn($value) => $value !== null);
    }
}
