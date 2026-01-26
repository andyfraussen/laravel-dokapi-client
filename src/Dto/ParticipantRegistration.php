<?php

namespace AndyFraussen\Dokapi\Dto;

final class ParticipantRegistration
{
    public readonly string $ulid;
    public readonly string $countryCode;
    public readonly string $creationTimestamp;
    public readonly string $lastModifiedTimestamp;
    public readonly ParticipantIdentifier $participantIdentifier;

    private function __construct(
        string $ulid,
        string $countryCode,
        string $creationTimestamp,
        string $lastModifiedTimestamp,
        ParticipantIdentifier $participantIdentifier
    ) {
        $this->ulid = $ulid;
        $this->countryCode = $countryCode;
        $this->creationTimestamp = $creationTimestamp;
        $this->lastModifiedTimestamp = $lastModifiedTimestamp;
        $this->participantIdentifier = $participantIdentifier;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['ulid'] ?? ''),
            (string) ($data['countryCode'] ?? ''),
            (string) ($data['creationTimestamp'] ?? ''),
            (string) ($data['lastModifiedTimestamp'] ?? ''),
            ParticipantIdentifier::fromArray((array) ($data['participantIdentifier'] ?? []))
        );
    }
}
