<?php

namespace AndyFraussen\Dokapi\Api;

use AndyFraussen\Dokapi\Api\Resources\ClientResource;
use AndyFraussen\Dokapi\Api\Resources\CountersResource;
use AndyFraussen\Dokapi\Api\Resources\IncomingDocumentsResource;
use AndyFraussen\Dokapi\Api\Resources\OutgoingDocumentsResource;
use AndyFraussen\Dokapi\Api\Resources\ParticipantsResource;
use AndyFraussen\Dokapi\Api\Resources\StatusResource;
use AndyFraussen\Dokapi\Api\Resources\ValidationsResource;
use AndyFraussen\Dokapi\Api\Resources\WebhooksResource;
use AndyFraussen\Dokapi\Clients\DokapiClient;

class DokapiApi
{
    public readonly StatusResource $status;
    public readonly WebhooksResource $webhooks;
    public readonly ParticipantsResource $participants;
    public readonly OutgoingDocumentsResource $outgoingDocuments;
    public readonly ValidationsResource $validations;
    public readonly IncomingDocumentsResource $incomingDocuments;
    public readonly CountersResource $counters;
    public readonly ClientResource $client;

    public function __construct(DokapiClient $client)
    {
        $this->status = new StatusResource($client);
        $this->webhooks = new WebhooksResource($client);
        $this->participants = new ParticipantsResource($client);
        $this->outgoingDocuments = new OutgoingDocumentsResource($client);
        $this->validations = new ValidationsResource($client);
        $this->incomingDocuments = new IncomingDocumentsResource($client);
        $this->counters = new CountersResource($client);
        $this->client = new ClientResource($client);
    }
}
