<?php

namespace AndyFraussen\Dokapi\Dto;

final class CreateWebhookResponse
{
    public readonly string $message;
    public readonly Webhook $webhook;

    private function __construct(string $message, Webhook $webhook)
    {
        $this->message = $message;
        $this->webhook = $webhook;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['message'] ?? ''),
            Webhook::fromArray((array) ($data['webhook'] ?? []))
        );
    }
}
