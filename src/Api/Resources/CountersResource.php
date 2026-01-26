<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\ClientCounterCollection;

class CountersResource extends BaseResource
{
    public function between(string $fromDate, string $toDate, ?string $flow = null): array
    {
        return $this->client->getClientCounters($fromDate, $toDate, $flow);
    }

    public function betweenDto(string $fromDate, string $toDate, ?string $flow = null): ClientCounterCollection
    {
        return $this->client->getClientCountersDto($fromDate, $toDate, $flow);
    }
}
