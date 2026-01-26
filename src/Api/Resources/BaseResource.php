<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use AndyFraussen\Dokapi\Requests\PayloadInterface;

abstract class BaseResource
{
    protected DokapiClient $client;

    public function __construct(DokapiClient $client)
    {
        $this->client = $client;
    }

    protected function payloadToArray(array|PayloadInterface $payload): array
    {
        return $payload instanceof PayloadInterface ? $payload->toArray() : $payload;
    }
}
