<?php

namespace AndyFraussen\Dokapi\Dto;

final class WebhookCollection
{
    /** @var Webhook[] */
    public readonly array $items;

    private function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn(array $item) => Webhook::fromArray($item),
            $data
        );

        return new self($items);
    }
}
