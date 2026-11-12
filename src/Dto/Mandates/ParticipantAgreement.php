<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class ParticipantAgreement implements PayloadInterface
{
    public function __construct(
        public readonly string $date,
        public readonly string $version,
        public readonly bool $paid,
        public readonly PersonalInformation $signer,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['date'] ?? ''),
            (string) ($data['version'] ?? ''),
            (bool) ($data['paid'] ?? false),
            PersonalInformation::fromArray((array) ($data['signer'] ?? [])),
        );
    }

    public function toArray(): array
    {
        return ['date' => $this->date, 'version' => $this->version, 'paid' => $this->paid, 'signer' => $this->signer->toArray()];
    }
}
