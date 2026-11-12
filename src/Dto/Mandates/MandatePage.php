<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

final class MandatePage
{
    /** @param list<Mandate> $items */
    private function __construct(
        public readonly array $items,
        public readonly float $count,
        public readonly ?string $lastEvaluatedKey,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            array_values(array_map(static fn(array $item) => Mandate::fromArray($item), (array) ($data['items'] ?? []))),
            (float) ($data['count'] ?? 0),
            isset($data['lastEvaluatedKey']) ? (string) $data['lastEvaluatedKey'] : null,
        );
    }
}
