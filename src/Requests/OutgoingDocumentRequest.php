<?php

namespace AndyFraussen\Dokapi\Requests;

use AndyFraussen\Dokapi\Dto\DocumentTypeIdentifier;
use AndyFraussen\Dokapi\Dto\ParticipantIdentifier;
use AndyFraussen\Dokapi\Dto\ProcessIdentifier;

final class OutgoingDocumentRequest implements PayloadInterface
{
    public readonly ParticipantIdentifier $sender;
    public readonly ParticipantIdentifier $receiver;
    public readonly string $c1CountryCode;
    public readonly DocumentTypeIdentifier $documentTypeIdentifier;
    public readonly ProcessIdentifier $processIdentifier;
    public readonly ?string $externalReference;

    public function __construct(
        ParticipantIdentifier $sender,
        ParticipantIdentifier $receiver,
        string $c1CountryCode,
        DocumentTypeIdentifier $documentTypeIdentifier,
        ProcessIdentifier $processIdentifier,
        ?string $externalReference = null
    ) {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->c1CountryCode = $c1CountryCode;
        $this->documentTypeIdentifier = $documentTypeIdentifier;
        $this->processIdentifier = $processIdentifier;
        $this->externalReference = $externalReference;
    }

    public function toArray(): array
    {
        $payload = [
            'sender' => $this->sender->toArray(),
            'receiver' => $this->receiver->toArray(),
            'c1CountryCode' => $this->c1CountryCode,
            'documentTypeIdentifier' => $this->documentTypeIdentifier->toArray(),
            'processIdentifier' => $this->processIdentifier->toArray(),
        ];

        if ($this->externalReference !== null) {
            $payload['externalReference'] = $this->externalReference;
        }

        return $payload;
    }
}
