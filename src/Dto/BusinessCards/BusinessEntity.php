<?php

namespace AndyFraussen\Dokapi\Dto\BusinessCards;

use AndyFraussen\Dokapi\Requests\PayloadInterface;
use InvalidArgumentException;

final class BusinessEntity implements PayloadInterface
{
    /**
     * @param list<MultilingualName> $name
     * @param list<BusinessIdentifier>|null $identifier
     * @param list<string>|null $websiteUri
     * @param list<BusinessContact>|null $contact
     */
    public function __construct(
        public readonly array $name,
        public readonly string $countryCode,
        public readonly ?string $geographicalInformation = null,
        public readonly ?array $identifier = null,
        public readonly ?array $websiteUri = null,
        public readonly ?array $contact = null,
        public readonly ?string $additionalInformation = null,
        public readonly ?string $registrationDate = null,
    ) {
        if ($name === []) {
            throw new InvalidArgumentException('A business entity needs at least one name.');
        }
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => array_map(static fn(MultilingualName $name) => $name->toArray(), $this->name),
            'countryCode' => $this->countryCode,
            'geographicalInformation' => $this->geographicalInformation,
            'identifier' => $this->identifier === null ? null : array_map(static fn(BusinessIdentifier $id) => $id->toArray(), $this->identifier),
            'websiteUri' => $this->websiteUri,
            'contact' => $this->contact === null ? null : array_map(static fn(BusinessContact $contact) => (object) $contact->toArray(), $this->contact),
            'additionalInformation' => $this->additionalInformation,
            'registrationDate' => $this->registrationDate,
        ], static fn($value) => $value !== null);
    }
}
