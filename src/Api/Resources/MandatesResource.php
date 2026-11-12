<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\Mandates\AdditionalDocumentUpload;
use AndyFraussen\Dokapi\Dto\Mandates\Mandate;
use AndyFraussen\Dokapi\Dto\Mandates\MandatePage;
use AndyFraussen\Dokapi\Dto\Mandates\MandateResponse;
use AndyFraussen\Dokapi\Requests\PayloadInterface;

class MandatesResource extends BaseResource
{
    public function requestBelgium(array|PayloadInterface $payload): array
    {
        return $this->client->requestBelgiumMandate($payload);
    }

    public function requestBelgiumDto(array|PayloadInterface $payload): MandateResponse
    {
        return $this->client->requestBelgiumMandateDto($payload);
    }

    public function get(string $ulid): array
    {
        return $this->client->getMandate($ulid);
    }

    public function getDto(string $ulid): Mandate
    {
        return $this->client->getMandateDto($ulid);
    }

    public function list(?string $lastEvaluatedKey = null, ?int $limit = null): array
    {
        return $this->client->listMandates($lastEvaluatedKey, $limit);
    }

    public function listDto(?string $lastEvaluatedKey = null, ?int $limit = null): MandatePage
    {
        return $this->client->listMandatesDto($lastEvaluatedKey, $limit);
    }

    public function uploadAdditionalDocument(AdditionalDocumentUpload|string $upload, string $content, string $contentType = 'application/pdf'): void
    {
        $this->client->uploadMandateDocument($upload instanceof AdditionalDocumentUpload ? $upload->url : $upload, $content, $contentType);
    }

    /** Register and manage webhooks on the Mandate API using the same subscription credentials. */
    public function webhooks(): WebhooksResource
    {
        return $this->client->forMandates()->api()->webhooks;
    }
}
