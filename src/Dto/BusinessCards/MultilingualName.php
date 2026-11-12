<?php

namespace AndyFraussen\Dokapi\Dto\BusinessCards;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class MultilingualName implements PayloadInterface
{
    public function __construct(public readonly string $value, public readonly ?string $language = null)
    {
    }

    public function toArray(): array
    {
        return array_filter(['value' => $this->value, 'language' => $this->language], static fn($value) => $value !== null);
    }
}
