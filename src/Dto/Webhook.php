<?php

namespace AndyFraussen\Dokapi\Dto;

final class Webhook
{
    public readonly string $ulid;
    public readonly string $clientUlid;
    public readonly string $creationTimestamp;
    public readonly string $lastModifiedTimestamp;
    public readonly string $url;
    /** @var string[] */
    public readonly array $events;

    private function __construct(
        string $ulid,
        string $clientUlid,
        string $creationTimestamp,
        string $lastModifiedTimestamp,
        string $url,
        array $events
    ) {
        $this->ulid = $ulid;
        $this->clientUlid = $clientUlid;
        $this->creationTimestamp = $creationTimestamp;
        $this->lastModifiedTimestamp = $lastModifiedTimestamp;
        $this->url = $url;
        $this->events = $events;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['ulid'] ?? ''),
            (string) ($data['clientUlid'] ?? ''),
            (string) ($data['creationTimestamp'] ?? ''),
            (string) ($data['lastModifiedTimestamp'] ?? ''),
            (string) ($data['url'] ?? ''),
            array_values((array) ($data['events'] ?? []))
        );
    }
}
