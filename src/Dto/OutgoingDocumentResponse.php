<?php

namespace AndyFraussen\Dokapi\Dto;

final class OutgoingDocumentResponse
{
    public readonly string $message;
    public readonly string $preSignedUploadUrl;
    public readonly OutgoingDocument $document;

    private function __construct(string $message, string $preSignedUploadUrl, OutgoingDocument $document)
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
            OutgoingDocument::fromArray((array) ($data['document'] ?? []))
        );
    }
}
