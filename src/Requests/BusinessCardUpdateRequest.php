<?php

namespace AndyFraussen\Dokapi\Requests;

use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Dto\BusinessCards\CompleteBusinessCard;
use InvalidArgumentException;

final class BusinessCardUpdateRequest implements PayloadInterface
{
    public readonly ParticipantIdentifier $participantIdentifier;
    /** @deprecated Use completeBusinessCard instead. */
    public readonly ?array $businessCardInfo;
    public readonly array|CompleteBusinessCard|null $completeBusinessCard;

    public function __construct(
        ParticipantIdentifier $participantIdentifier,
        ?array $businessCardInfo = null,
        array|CompleteBusinessCard|null $completeBusinessCard = null
    ) {
        if ($businessCardInfo !== null && $completeBusinessCard !== null) {
            throw new InvalidArgumentException('businessCardInfo and completeBusinessCard are mutually exclusive.');
        }

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
            $payload['completeBusinessCard'] = $this->completeBusinessCard instanceof CompleteBusinessCard
                ? $this->completeBusinessCard->toArray()
                : $this->completeBusinessCard;
        }

        return $payload;
    }
}
