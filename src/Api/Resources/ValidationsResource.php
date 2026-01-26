<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\ValidatingDocumentResponse;
use AndyFraussen\Dokapi\Requests\PayloadInterface;

class ValidationsResource extends BaseResource
{
    public function create(array|PayloadInterface $payload = []): array
    {
        return $this->client->createValidatingDocument($this->payloadToArray($payload));
    }

    public function createDto(array|PayloadInterface $payload = []): ValidatingDocumentResponse
    {
        return $this->client->createValidatingDocumentDto($this->payloadToArray($payload));
    }

    public function send(string $xmlContent, array|PayloadInterface $payload = []): array
    {
        return $this->client->sendValidatingDocument($xmlContent, $this->payloadToArray($payload));
    }

    public function sendDto(string $xmlContent, array|PayloadInterface $payload = []): ValidatingDocumentResponse
    {
        return $this->client->sendValidatingDocumentDto($xmlContent, $this->payloadToArray($payload));
    }
}
