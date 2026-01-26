<?php

namespace AndyFraussen\Dokapi\Dto;

final class ClientCounterCollection
{
    /** @var ClientCounter[] */
    public readonly array $items;

    private function __construct(array $items)
    {
        $this->items = $items;
    }

    public static function fromArray(array $data): self
    {
        $items = array_map(
            fn(array $item) => ClientCounter::fromArray($item),
            $data
        );

        return new self($items);
    }
}
