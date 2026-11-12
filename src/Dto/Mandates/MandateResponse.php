<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

final class MandateResponse
{
    private function __construct(
        public readonly string $message,
        public readonly Mandate $mandate,
        public readonly ?AdditionalDocumentUpload $additionalDocumentUpload,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['message'] ?? ''),
            Mandate::fromArray((array) ($data['mandate'] ?? [])),
            isset($data['additionalDocumentUpload']) ? AdditionalDocumentUpload::fromArray((array) $data['additionalDocumentUpload']) : null,
        );
    }
}
