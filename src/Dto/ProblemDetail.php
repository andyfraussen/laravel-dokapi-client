<?php

namespace AndyFraussen\Dokapi\Dto;

final class ProblemDetail
{
    public readonly string $title;
    public readonly string $detail;
    public readonly ?string $type;
    public readonly ?string $instance;
    public readonly ?int $status;
    public readonly ?string $uuid;

    private function __construct(
        string $title,
        string $detail,
        ?string $type,
        ?string $instance,
        ?int $status,
        ?string $uuid
    ) {
        $this->title = $title;
        $this->detail = $detail;
        $this->type = $type;
        $this->instance = $instance;
        $this->status = $status;
        $this->uuid = $uuid;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['title'] ?? ''),
            (string) ($data['detail'] ?? ''),
            isset($data['type']) ? (string) $data['type'] : null,
            isset($data['instance']) ? (string) $data['instance'] : null,
            isset($data['status']) ? (int) $data['status'] : null,
            isset($data['uuid']) ? (string) $data['uuid'] : null
        );
    }

    public static function isProblemDetail(array $data): bool
    {
        return array_key_exists('title', $data) && array_key_exists('detail', $data);
    }
}
