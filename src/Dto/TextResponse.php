<?php

namespace AndyFraussen\Dokapi\Dto;

final class TextResponse
{
    public readonly string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
