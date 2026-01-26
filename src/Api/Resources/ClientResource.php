<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\ClientCancellationResponse;

class ClientResource extends BaseResource
{
    public function deactivate(): array
    {
        return $this->client->deactivateClientRegistration();
    }

    public function deactivateDto(): ClientCancellationResponse
    {
        return $this->client->deactivateClientRegistrationDto();
    }
}
