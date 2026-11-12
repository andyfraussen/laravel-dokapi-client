<?php

namespace AndyFraussen\Dokapi\Dto;

final class MandatoryDocumentType
{
    private function __construct(
        public readonly DocumentTypeIdentifier $documentTypeIdentifier,
        public readonly ProcessIdentifier $processIdentifier,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            DocumentTypeIdentifier::fromArray((array) ($data['documentTypeIdentifier'] ?? [])),
            ProcessIdentifier::fromArray((array) ($data['processIdentifier'] ?? [])),
        );
    }
}
