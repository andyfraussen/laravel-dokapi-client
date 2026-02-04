<?php

namespace AndyFraussen\Dokapi\Dto;

final class ParticipantRegistrationPage
{
    /** @var ParticipantRegistration[] */
    public readonly array $items;
    public readonly float $count;
    public readonly ?string $lastEvaluatedKey;

    private function __construct(array $items, float $count, ?string $lastEvaluatedKey)
    {
        $this->items = $items;
        $this->count = $count;
        $this->lastEvaluatedKey = $lastEvaluatedKey;
    }

    public static function fromArray(array $data): self
    {
        $itemsRaw = $data['Items'] ?? $data['items'] ?? [];
        $countRaw = $data['Count'] ?? $data['count'] ?? 0;
        $lastKeyRaw = $data['LastEvaluatedKey'] ?? $data['lastEvaluatedKey'] ?? null;

        $items = array_map(
            fn(array $item) => ParticipantRegistration::fromArray($item),
            (array) $itemsRaw
        );

        return new self(
            $items,
            (float) $countRaw,
            isset($lastKeyRaw) ? (string) $lastKeyRaw : null
        );
    }
}
