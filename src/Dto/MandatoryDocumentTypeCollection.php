<?php

namespace AndyFraussen\Dokapi\Dto;

final class MandatoryDocumentTypeCollection
{
    /** @param list<MandatoryDocumentType> $items */
    private function __construct(public readonly array $items)
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(array_values(array_map(static fn(array $item) => MandatoryDocumentType::fromArray($item), $data)));
    }
}
