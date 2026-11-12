<?php

namespace AndyFraussen\Dokapi\Dto\BusinessCards;

use AndyFraussen\Dokapi\Requests\PayloadInterface;

final class BusinessIdentifier implements PayloadInterface
{
    public function __construct(public readonly string $value, public readonly string $scheme)
    {
    }

    public function toArray(): array
    {
        return ['value' => $this->value, 'scheme' => $this->scheme];
    }
}
