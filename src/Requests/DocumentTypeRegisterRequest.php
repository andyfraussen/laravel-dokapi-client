<?php

namespace AndyFraussen\Dokapi\Requests;

use AndyFraussen\Dokapi\Dto\DocumentTypeIdentifier;
use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Dto\ProcessIdentifier;

final class DocumentTypeRegisterRequest implements PayloadInterface
{
    public readonly ParticipantIdentifier $participantIdentifier;
    public readonly DocumentTypeIdentifier $documentTypeIdentifier;
    public readonly ProcessIdentifier $processIdentifier;

    public function __construct(
        ParticipantIdentifier $participantIdentifier,
        DocumentTypeIdentifier $documentTypeIdentifier,
        ProcessIdentifier $processIdentifier
    ) {
        $this->participantIdentifier = $participantIdentifier;
        $this->documentTypeIdentifier = $documentTypeIdentifier;
        $this->processIdentifier = $processIdentifier;
    }

    public function toArray(): array
    {
        return [
            'participantIdentifier' => $this->participantIdentifier->toArray(),
            'documentTypeIdentifier' => $this->documentTypeIdentifier->toArray(),
            'processIdentifier' => $this->processIdentifier->toArray(),
        ];
    }
}
