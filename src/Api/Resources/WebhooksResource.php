<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\CreateWebhookResponse;
use AndyFraussen\Dokapi\Dto\DeleteWebhookResponse;
use AndyFraussen\Dokapi\Dto\TextResponse;
use AndyFraussen\Dokapi\Dto\WebhookCollection;
use AndyFraussen\Dokapi\Dto\Mandates\MandateFeedback;
use AndyFraussen\Dokapi\Exceptions\DokapiException;
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

    /** Authenticate the exact raw request body before decoding its mandate feedback. */
    public function parseMandateFeedback(string $payload, string $signature, string $secret): MandateFeedback
    {
        if (!$this->verifySignature($payload, $signature, $secret)) {
            throw new DokapiException('Invalid Dokapi webhook signature.');
        }

        try {
            $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data)) {
                throw new \InvalidArgumentException('Expected a JSON object for mandate feedback.');
            }

            return MandateFeedback::fromArray($data);
        } catch (\JsonException|\InvalidArgumentException $exception) {
            throw new DokapiException('Invalid Dokapi mandate feedback: ' . $exception->getMessage(), 0, $exception);
        }
    }
}
