<?php

namespace AndyFraussen\Dokapi\Requests;

use AndyFraussen\Dokapi\Dto\Mandates\KycData;
use AndyFraussen\Dokapi\Dto\Mandates\PeppolEntity;
use DateTimeImmutable;
use InvalidArgumentException;

final class BelgiumMandateRequest implements PayloadInterface
{
    /** @param list<PeppolEntity>|null $peppolEntities Omit to let Dokapi use its default entities. */
    public function __construct(
        public readonly string $startDate,
        public readonly string $kbo,
        public readonly KycData $kycData,
        public readonly ?array $peppolEntities = null,
        public readonly ?string $endDate = null,
    ) {
        foreach ([$startDate, $endDate] as $date) {
            $parsed = $date === null ? false : DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if ($date !== null && (!$parsed || $parsed->format('Y-m-d') !== $date)) {
                throw new InvalidArgumentException('Mandate dates must be calendar dates in YYYY-MM-DD format.');
            }
        }
        if ($endDate !== null && $endDate <= $startDate) {
            throw new InvalidArgumentException('endDate must be strictly after startDate.');
        }
        if ($peppolEntities !== null && count($peppolEntities) > 10) {
            throw new InvalidArgumentException('A mandate supports at most 10 Peppol entities.');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'startDate' => $this->startDate,
            'identifier' => ['kbo' => $this->kbo],
            'kycData' => $this->kycData->toArray(),
            'peppolEntities' => $this->peppolEntities === null ? null : array_map(static fn(PeppolEntity $entity) => $entity->toArray(), $this->peppolEntities),
            'endDate' => $this->endDate,
        ], static fn($value) => $value !== null);
    }
}
