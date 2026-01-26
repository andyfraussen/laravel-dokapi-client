<?php

namespace AndyFraussen\Dokapi\Api\Resources;

use AndyFraussen\Dokapi\Dto\CreateParticipantRegistrationResponse;
use AndyFraussen\Dokapi\Dto\ParticipantRegistration;
use AndyFraussen\Dokapi\Dto\ParticipantRegistrationPage;
use AndyFraussen\Dokapi\Dto\RegisterDocumentTypeResponse;
use AndyFraussen\Dokapi\Dto\ServiceGroupAndBusinessCard;
use AndyFraussen\Dokapi\Dto\TextResponse;
use AndyFraussen\Dokapi\Requests\PayloadInterface;

class ParticipantsResource extends BaseResource
{
    public function list(?string $lastEvaluatedKey = null, ?int $limit = null): array
    {
        return $this->client->listParticipantRegistrations($lastEvaluatedKey, $limit);
    }

    public function listDto(?string $lastEvaluatedKey = null, ?int $limit = null): ParticipantRegistrationPage
    {
        return $this->client->listParticipantRegistrationsDto($lastEvaluatedKey, $limit);
    }

    public function lookup(string $value, ?string $scheme = null): array
    {
        return $this->client->lookupParticipant($value, $scheme);
    }

    public function lookupDto(string $value, ?string $scheme = null): ServiceGroupAndBusinessCard
    {
        return $this->client->lookupParticipantDto($value, $scheme);
    }

    public function find(string $value, ?string $scheme = null): array
    {
        return $this->client->findParticipant($value, $scheme);
    }

    public function findDto(string $value, ?string $scheme = null): ParticipantRegistration
    {
        return $this->client->findParticipantDto($value, $scheme);
    }

    public function register(array|PayloadInterface $payload): array
    {
        return $this->client->registerParticipant($this->payloadToArray($payload));
    }

    public function registerDto(array|PayloadInterface $payload): CreateParticipantRegistrationResponse
    {
        return $this->client->registerParticipantDto($this->payloadToArray($payload));
    }

    public function deregister(array|PayloadInterface $payload): string
    {
        return $this->client->deregisterParticipant($this->payloadToArray($payload));
    }

    public function deregisterDto(array|PayloadInterface $payload): TextResponse
    {
        return $this->client->deregisterParticipantDto($this->payloadToArray($payload));
    }

    public function updateBusinessCard(array|PayloadInterface $payload): string
    {
        return $this->client->updateBusinessCard($this->payloadToArray($payload));
    }

    public function updateBusinessCardDto(array|PayloadInterface $payload): TextResponse
    {
        return $this->client->updateBusinessCardDto($this->payloadToArray($payload));
    }

    public function pushBusinessCard(array|PayloadInterface $payload): string
    {
        return $this->client->pushBusinessCard($this->payloadToArray($payload));
    }

    public function pushBusinessCardDto(array|PayloadInterface $payload): TextResponse
    {
        return $this->client->pushBusinessCardDto($this->payloadToArray($payload));
    }

    public function registerDocumentType(array|PayloadInterface $payload): array
    {
        return $this->client->registerDocumentType($this->payloadToArray($payload));
    }

    public function registerDocumentTypeDto(array|PayloadInterface $payload): RegisterDocumentTypeResponse
    {
        return $this->client->registerDocumentTypeDto($this->payloadToArray($payload));
    }

    public function deregisterDocumentType(array|PayloadInterface $payload): string
    {
        return $this->client->deregisterDocumentType($this->payloadToArray($payload));
    }

    public function deregisterDocumentTypeDto(array|PayloadInterface $payload): TextResponse
    {
        return $this->client->deregisterDocumentTypeDto($this->payloadToArray($payload));
    }
}
