<?php

namespace AndyFraussen\Dokapi\Requests;

use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;

final class BusinessCardUpdateRequest implements PayloadInterface
{
    public readonly ParticipantIdentifier $participantIdentifier;
    public readonly ?array $businessCardInfo;
    public readonly ?array $completeBusinessCard;

    public function __construct(
        ParticipantIdentifier $participantIdentifier,
        ?array $businessCardInfo = null,
        ?array $completeBusinessCard = null
    ) {
        $this->participantIdentifier = $participantIdentifier;
        $this->businessCardInfo = $businessCardInfo;
        $this->completeBusinessCard = $completeBusinessCard;
    }

    public function toArray(): array
    {
        $payload = [
            'participantIdentifier' => $this->participantIdentifier->toArray(),
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
