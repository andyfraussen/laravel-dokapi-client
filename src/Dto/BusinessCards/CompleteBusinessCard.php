<?php

namespace AndyFraussen\Dokapi\Dto\BusinessCards;

use AndyFraussen\Dokapi\Requests\PayloadInterface;
use InvalidArgumentException;

final class CompleteBusinessCard implements PayloadInterface
{
    /** @param list<BusinessEntity> $businessEntity */
    public function __construct(public readonly array $businessEntity)
    {
        if ($businessEntity === []) {
            throw new InvalidArgumentException('A complete business card needs at least one business entity.');
        }
    }

    public function toArray(): array
    {
        return ['businessEntity' => array_map(static fn(BusinessEntity $entity) => $entity->toArray(), $this->businessEntity)];
    }
}
