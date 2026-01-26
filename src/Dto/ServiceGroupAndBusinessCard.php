<?php

namespace AndyFraussen\Dokapi\Dto;

final class ServiceGroupAndBusinessCard
{
    public readonly array $serviceGroup;
    public readonly array $serviceMetadata;
    public readonly array $completeServiceGroup;
    public readonly array $businessCard;

    private function __construct(array $serviceGroup, array $serviceMetadata, array $completeServiceGroup, array $businessCard)
    {
        $this->serviceGroup = $serviceGroup;
        $this->serviceMetadata = $serviceMetadata;
        $this->completeServiceGroup = $completeServiceGroup;
        $this->businessCard = $businessCard;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (array) ($data['serviceGroup'] ?? []),
            (array) ($data['serviceMetadata'] ?? []),
            (array) ($data['completeServiceGroup'] ?? []),
            (array) ($data['businessCard'] ?? [])
        );
    }
}
