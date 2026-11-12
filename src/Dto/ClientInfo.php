<?php

namespace AndyFraussen\Dokapi\Dto;

final class ClientInfo
{
    /** @param array<string, string> $customAttributes */
    private function __construct(
        public readonly string $ulid,
        public readonly string $status,
        public readonly string $creationTimestamp,
        public readonly string $lastModifiedTimestamp,
        public readonly ?bool $subscriptionCancelled,
        public readonly ?string $clientReference,
        public readonly ?string $organizationReference,
        public readonly ?string $apiableSubscriptionId,
        public readonly ?string $apiablePlanId,
        public readonly array $customAttributes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['ulid'] ?? ''),
            (string) ($data['status'] ?? ''),
            (string) ($data['creationTimestamp'] ?? ''),
            (string) ($data['lastModifiedTimestamp'] ?? ''),
            isset($data['subscriptionCancelled']) ? (bool) $data['subscriptionCancelled'] : null,
            isset($data['clientReference']) ? (string) $data['clientReference'] : null,
            isset($data['organizationReference']) ? (string) $data['organizationReference'] : null,
            isset($data['apiableSubscriptionId']) ? (string) $data['apiableSubscriptionId'] : null,
            isset($data['apiableInformation']['apiablePlanId']) ? (string) $data['apiableInformation']['apiablePlanId'] : null,
            (array) ($data['customAttributes'] ?? []),
        );
    }
}
