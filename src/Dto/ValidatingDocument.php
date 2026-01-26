<?php

namespace AndyFraussen\Dokapi\Dto;

final class ValidatingDocument
{
    public readonly string $ulid;
    public readonly string $creationTimestamp;
    public readonly string $lastModifiedTimestamp;
    public readonly ?string $externalReference;
    public readonly string $status;
    public readonly string $statusMessage;
    /** @var ValidatingDocumentStatusHistoryItem[] */
    public readonly array $statusHistory;

    private function __construct(
        string $ulid,
        string $creationTimestamp,
        string $lastModifiedTimestamp,
        ?string $externalReference,
        string $status,
        string $statusMessage,
        array $statusHistory
    ) {
        $this->ulid = $ulid;
        $this->creationTimestamp = $creationTimestamp;
        $this->lastModifiedTimestamp = $lastModifiedTimestamp;
        $this->externalReference = $externalReference;
        $this->status = $status;
        $this->statusMessage = $statusMessage;
        $this->statusHistory = $statusHistory;
    }

    public static function fromArray(array $data): self
    {
        $history = array_map(
            fn(array $item) => ValidatingDocumentStatusHistoryItem::fromArray($item),
            (array) ($data['statusHistory'] ?? [])
        );

        return new self(
            (string) ($data['ulid'] ?? ''),
            (string) ($data['creationTimestamp'] ?? ''),
            (string) ($data['lastModifiedTimestamp'] ?? ''),
            isset($data['externalReference']) ? (string) $data['externalReference'] : null,
            (string) ($data['status'] ?? ''),
            (string) ($data['statusMessage'] ?? ''),
            $history
        );
    }
}
