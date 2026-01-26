<?php

namespace AndyFraussen\Dokapi\Dto;

final class OutgoingDocumentStatusHistoryItem
{
    public readonly string $status;
    public readonly string $statusMessage;
    public readonly string $timestamp;

    private function __construct(string $status, string $statusMessage, string $timestamp)
    {
        $this->status = $status;
        $this->statusMessage = $statusMessage;
        $this->timestamp = $timestamp;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['status'] ?? ''),
            (string) ($data['statusMessage'] ?? ''),
            (string) ($data['timestamp'] ?? '')
        );
    }
}
