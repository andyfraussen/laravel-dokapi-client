<?php

namespace AndyFraussen\Dokapi\Dto;

final class ValidatingDocumentResponse
{
    public readonly string $message;
    public readonly string $preSignedUploadUrl;
    public readonly ValidatingDocument $document;

    private function __construct(string $message, string $preSignedUploadUrl, ValidatingDocument $document)
    {
        $this->message = $message;
        $this->preSignedUploadUrl = $preSignedUploadUrl;
        $this->document = $document;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['message'] ?? ''),
            (string) ($data['preSignedUploadUrl'] ?? ''),
            ValidatingDocument::fromArray((array) ($data['document'] ?? []))
        );
    }
}
