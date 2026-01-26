<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\TextResponse;

class StatusResource extends BaseResource
{
    public function get(): string
    {
        return $this->client->getStatus();
    }

    public function getDto(): TextResponse
    {
        return $this->client->getStatusDto();
    }
}
