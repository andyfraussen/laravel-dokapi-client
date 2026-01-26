<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\CreateWebhookResponse;
use AndyFraussen\Dokapi\Dto\DeleteWebhookResponse;
use AndyFraussen\Dokapi\Dto\TextResponse;
use AndyFraussen\Dokapi\Dto\WebhookCollection;
use AndyFraussen\Dokapi\Requests\PayloadInterface;

class WebhooksResource extends BaseResource
{
    public function all(): array
    {
        return $this->client->listWebhooks();
    }

    public function allDto(): WebhookCollection
    {
        return $this->client->listWebhooksDto();
    }

    public function create(array|PayloadInterface $payload): array
    {
        return $this->client->createWebhook($this->payloadToArray($payload));
    }

    public function createDto(array|PayloadInterface $payload): CreateWebhookResponse
    {
        return $this->client->createWebhookDto($this->payloadToArray($payload));
    }

    public function delete(string $ulid): array
    {
        return $this->client->deleteWebhook($ulid);
    }

    public function deleteDto(string $ulid): DeleteWebhookResponse
    {
        return $this->client->deleteWebhookDto($ulid);
    }

    public function secret(): string
    {
        return $this->client->generateWebhookSecret();
    }

    public function secretDto(): TextResponse
    {
        return $this->client->generateWebhookSecretDto();
    }

    public function verifySignature(string $payload, string $signature, string $secret): bool
    {
        return $this->client->verifyWebhookSignature($payload, $signature, $secret);
    }
}
