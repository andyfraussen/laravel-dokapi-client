<?php

namespace AndyFraussen\Dokapi\Requests;

use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;

final class ParticipantRegistrationRequest implements PayloadInterface
{
    public readonly ParticipantIdentifier $participantIdentifier;
    public readonly string $countryCode;
    public readonly ?array $businessCardInfo;
    public readonly ?array $completeBusinessCard;

    public function __construct(
        ParticipantIdentifier $participantIdentifier,
        string $countryCode,
        ?array $businessCardInfo = null,
        ?array $completeBusinessCard = null
    ) {
        $this->participantIdentifier = $participantIdentifier;
        $this->countryCode = $countryCode;
        $this->businessCardInfo = $businessCardInfo;
        $this->completeBusinessCard = $completeBusinessCard;
    }

    public function toArray(): array
    {
        $payload = [
            'participantIdentifier' => $this->participantIdentifier->toArray(),
            'countryCode' => $this->countryCode,
        ];

        if ($this->businessCardInfo !== null) {
            $payload['businessCardInfo'] = $this->businessCardInfo;
        }

        if ($this->completeBusinessCard !== null) {
            $payload['completeBusinessCard'] = $this->completeBusinessCard;
        }

        return $payload;
    }
}
