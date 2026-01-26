<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\TextResponse;

class IncomingDocumentsResource extends BaseResource
{
    public function generatePresignedUrl(string $documentUlid): string
    {
        return $this->client->generateIncomingPresignedUrl($documentUlid);
    }

    public function generatePresignedUrlDto(string $documentUlid): TextResponse
    {
        return $this->client->generateIncomingPresignedUrlDto($documentUlid);
    }

    public function confirm(string $documentUlid): string
    {
        return $this->client->confirmIncomingDocument($documentUlid);
    }

    public function confirmDto(string $documentUlid): TextResponse
    {
        return $this->client->confirmIncomingDocumentDto($documentUlid);
    }
}
