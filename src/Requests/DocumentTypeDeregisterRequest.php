<?php

namespace AndyFraussen\Dokapi\Requests;

use AndyFraussen\Dokapi\Dto\DocumentTypeIdentifier;
use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;

final class DocumentTypeDeregisterRequest implements PayloadInterface
{
    public readonly ParticipantIdentifier $participantIdentifier;
    public readonly DocumentTypeIdentifier $documentTypeIdentifier;

    public function __construct(ParticipantIdentifier $participantIdentifier, DocumentTypeIdentifier $documentTypeIdentifier)
    {
        $this->participantIdentifier = $participantIdentifier;
        $this->documentTypeIdentifier = $documentTypeIdentifier;
    }

    public function toArray(): array
    {
        return [
            'participantIdentifier' => $this->participantIdentifier->toArray(),
            'documentTypeIdentifier' => $this->documentTypeIdentifier->toArray(),
        ];
    }
}
