<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

final class AdditionalDocumentUpload
{
    private function __construct(public readonly string $key, public readonly string $url)
    {
    }

    public static function fromArray(array $data): self
    {
        return new self((string) ($data['key'] ?? ''), (string) ($data['url'] ?? ''));
    }
}
