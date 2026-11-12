<?php

namespace AndyFraussen\Dokapi\Dto\Mandates;

use AndyFraussen\Dokapi\Enums\MandateEvent;
use InvalidArgumentException;

final class MandateFeedback
{
    private function __construct(
        public readonly MandateEvent|string $event,
        public readonly string $ulid,
        public readonly MandateStatusInformation|MandateCapability $body,
    ) {
    }

    /** Parse an already authenticated webhook payload. Verify its raw-body signature first. */
    public static function fromArray(array $data): self
    {
        $event = $data['event'] ?? null;
        if (!is_string($event) || !str_starts_with($event, 'mandate.')
            || !isset($data['ulid']) || !is_string($data['ulid']) || $data['ulid'] === ''
            || !isset($data['body']) || !is_array($data['body'])) {
            throw new InvalidArgumentException('Expected a mandate webhook event, invocation ULID and body.');
        }

        $capabilityEvent = str_starts_with($event, 'mandate.capability.');
        $required = $capabilityEvent
            ? ['ulid', 'mandateUlid', 'clientUlid', 'participantIdentifier', 'scope', 'status', 'statusMessage', 'startDate']
            : ['ulid', 'clientUlid', 'status', 'statusMessage', 'identifier'];
        foreach ($required as $field) {
            $value = $data['body'][$field] ?? null;
            $isObject = in_array($field, ['participantIdentifier', 'identifier'], true);
            if ($isObject ? !is_array($value) : !is_string($value)) {
                throw new InvalidArgumentException('Missing or invalid mandate webhook body field: ' . $field);
            }
        }
        if ($capabilityEvent) {
            self::validateParticipantIdentifier($data['body']['participantIdentifier']);
            foreach (['endDate', 'activatedAt'] as $field) {
                if (isset($data['body'][$field]) && !is_string($data['body'][$field])) {
                    throw new InvalidArgumentException('Invalid mandate capability field: ' . $field);
                }
            }
        } else {
            foreach ($data['body']['identifier'] as $value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException('Mandate identifiers must contain string values.');
                }
            }
            if (isset($data['body']['peppolEntities'])) {
                if (!is_array($data['body']['peppolEntities']) || !array_is_list($data['body']['peppolEntities'])) {
                    throw new InvalidArgumentException('Expected a list of Peppol entities.');
                }
                foreach ($data['body']['peppolEntities'] as $entity) {
                    if (!is_array($entity) || !is_array($entity['participantIdentifier'] ?? null)) {
                        throw new InvalidArgumentException('Expected a Peppol entity with a participant identifier.');
                    }
                    self::validateParticipantIdentifier($entity['participantIdentifier']);
                    foreach (['sendingCapabilities', 'receivingCapabilities'] as $flag) {
                        if (isset($entity[$flag]) && !is_bool($entity[$flag])) {
                            throw new InvalidArgumentException('Peppol entity capability flags must be booleans.');
                        }
                    }
                }
            }
        }

        return new self(
            MandateEvent::tryFrom($event) ?? $event,
            $data['ulid'],
            $capabilityEvent ? MandateCapability::fromArray($data['body']) : MandateStatusInformation::fromArray($data['body']),
        );
    }

    private static function validateParticipantIdentifier(array $identifier): void
    {
        if (!is_string($identifier['value'] ?? null)
            || (isset($identifier['scheme']) && !is_string($identifier['scheme']))) {
            throw new InvalidArgumentException('Expected a participant identifier value and optional scheme as strings.');
        }
    }
}
