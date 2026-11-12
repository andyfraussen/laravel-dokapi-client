<?php

namespace AndyFraussen\Dokapi\Exceptions;

/** The API created a document, but its upload did not complete successfully. */
class DokapiUploadException extends DokapiException
{
    public function __construct(private readonly array $createResponse, DokapiException $previous)
    {
        parent::__construct('Dokapi created the document, but the upload failed. Reconcile this document before submitting it again.', 0, $previous);
    }

    public function getCreateResponse(): array
    {
        return $this->createResponse;
    }

    public function getDocumentUlid(): ?string
    {
        $ulid = $this->createResponse['document']['ulid'] ?? null;

        return is_string($ulid) ? $ulid : null;
    }

    public function getUploadUrl(): ?string
    {
        $url = $this->createResponse['preSignedUploadUrl'] ?? null;

        return is_string($url) ? $url : null;
    }
}
