<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\ClientCancellationResponse;
use AndyFraussen\Dokapi\Dto\ClientInfo;

class ClientResource extends BaseResource
{
    public function info(): array
    {
        return $this->client->getClientInfo();
    }

    public function infoDto(): ClientInfo
    {
        return $this->client->getClientInfoDto();
    }

    public function deactivate(): array
    {
        return $this->client->deactivateClientRegistration();
    }

    public function deactivateDto(): ClientCancellationResponse
    {
        return $this->client->deactivateClientRegistrationDto();
    }
}
