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
        $items = array_map(
            fn(array $item) => ParticipantRegistration::fromArray($item),
            (array) ($data['Items'] ?? [])
        );

        return new self(
            $items,
            (float) ($data['Count'] ?? 0),
            isset($data['LastEvaluatedKey']) ? (string) $data['LastEvaluatedKey'] : null
        );
    }
}
