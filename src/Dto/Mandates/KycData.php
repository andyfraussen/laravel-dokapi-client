<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class KycData implements PayloadInterface
{
    /** @param list<string> $additionalDocuments Read-only uploaded document keys returned by Dokapi. */
    public function __construct(
        public readonly KycCompany $company,
        public readonly ?KycRepresentative $representative = null,
        public readonly ?ParticipantAgreement $participantAgreement = null,
        public readonly array $additionalDocuments = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            KycCompany::fromArray((array) ($data['company'] ?? [])),
            isset($data['representative']) ? KycRepresentative::fromArray((array) $data['representative']) : null,
            isset($data['participantAgreement']) ? ParticipantAgreement::fromArray((array) $data['participantAgreement']) : null,
            array_values((array) ($data['additionalDocuments'] ?? [])),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'company' => $this->company->toArray(),
            'representative' => $this->representative?->toArray(),
            'participantAgreement' => $this->participantAgreement?->toArray(),
        ], static fn($value) => $value !== null);
    }
}
