<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\OutgoingDocumentResponse;
use AndyFraussen\Dokapi\Requests\PayloadInterface;

class OutgoingDocumentsResource extends BaseResource
{
    public function create(array|PayloadInterface $payload): array
    {
        return $this->client->createOutgoingDocument($this->payloadToArray($payload));
    }

    public function createDto(array|PayloadInterface $payload): OutgoingDocumentResponse
    {
        return $this->client->createOutgoingDocumentDto($this->payloadToArray($payload));
    }

    public function send(array|PayloadInterface $payload, string $xmlContent): array
    {
        return $this->client->sendOutgoingDocument($this->payloadToArray($payload), $xmlContent);
    }

    public function sendDto(array|PayloadInterface $payload, string $xmlContent): OutgoingDocumentResponse
    {
        return $this->client->sendOutgoingDocumentDto($this->payloadToArray($payload), $xmlContent);
    }
}
