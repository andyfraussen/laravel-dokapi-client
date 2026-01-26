<?php

namespace AndyFraussen\Dokapi\Dto;

final class ClientCounter
{
    public readonly string $clientUlid;
    public readonly string $creationTimestamp;
    public readonly string $lastModifiedTimestamp;
    public readonly string $flow;
    public readonly float $count;

    private function __construct(string $clientUlid, string $creationTimestamp, string $lastModifiedTimestamp, string $flow, float $count)
    {
        $this->clientUlid = $clientUlid;
        $this->creationTimestamp = $creationTimestamp;
        $this->lastModifiedTimestamp = $lastModifiedTimestamp;
        $this->flow = $flow;
        $this->count = $count;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['clientUlid'] ?? ''),
            (string) ($data['creationTimestamp'] ?? ''),
            (string) ($data['lastModifiedTimestamp'] ?? ''),
            (string) ($data['flow'] ?? ''),
            (float) ($data['count'] ?? 0)
        );
    }
}
