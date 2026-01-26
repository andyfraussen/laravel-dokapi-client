<?php

namespace AndyFraussen\Dokapi\Dto;

final class OutgoingDocument
{
    public readonly string $ulid;
    public readonly string $status;
    public readonly string $statusMessage;
    public readonly string $creationTimestamp;
    public readonly string $lastModifiedTimestamp;
    public readonly ParticipantIdentifier $sender;
    public readonly ParticipantIdentifier $receiver;
    public readonly string $c1CountryCode;
    public readonly DocumentTypeIdentifier $documentTypeIdentifier;
    public readonly ProcessIdentifier $processIdentifier;
    public readonly ?string $externalReference;
    /** @var OutgoingDocumentStatusHistoryItem[] */
    public readonly array $statusHistory;

    private function __construct(
        string $ulid,
        string $status,
        string $statusMessage,
        string $creationTimestamp,
        string $lastModifiedTimestamp,
        ParticipantIdentifier $sender,
        ParticipantIdentifier $receiver,
        string $c1CountryCode,
        DocumentTypeIdentifier $documentTypeIdentifier,
        ProcessIdentifier $processIdentifier,
        ?string $externalReference,
        array $statusHistory
    ) {
        $this->ulid = $ulid;
        $this->status = $status;
        $this->statusMessage = $statusMessage;
        $this->creationTimestamp = $creationTimestamp;
        $this->lastModifiedTimestamp = $lastModifiedTimestamp;
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->c1CountryCode = $c1CountryCode;
        $this->documentTypeIdentifier = $documentTypeIdentifier;
        $this->processIdentifier = $processIdentifier;
        $this->externalReference = $externalReference;
        $this->statusHistory = $statusHistory;
    }

    public static function fromArray(array $data): self
    {
        $history = array_map(
            fn(array $item) => OutgoingDocumentStatusHistoryItem::fromArray($item),
            (array) ($data['statusHistory'] ?? [])
        );

        return new self(
            (string) ($data['ulid'] ?? ''),
            (string) ($data['status'] ?? ''),
            (string) ($data['statusMessage'] ?? ''),
            (string) ($data['creationTimestamp'] ?? ''),
            (string) ($data['lastModifiedTimestamp'] ?? ''),
            ParticipantIdentifier::fromArray((array) ($data['sender'] ?? [])),
            ParticipantIdentifier::fromArray((array) ($data['receiver'] ?? [])),
            (string) ($data['c1CountryCode'] ?? ''),
            DocumentTypeIdentifier::fromArray((array) ($data['documentTypeIdentifier'] ?? [])),
            ProcessIdentifier::fromArray((array) ($data['processIdentifier'] ?? [])),
            isset($data['externalReference']) ? (string) $data['externalReference'] : null,
            $history
        );
    }
}
