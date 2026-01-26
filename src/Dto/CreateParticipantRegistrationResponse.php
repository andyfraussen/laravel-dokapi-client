<?php

namespace AndyFraussen\Dokapi\Dto;

final class CreateParticipantRegistrationResponse
{
    public readonly string $participantRegistrationMessage;
    public readonly bool $participantRegistrationSuccessful;
    public readonly string $businessCardMessage;
    public readonly bool $businessCardSuccessful;
    public readonly ParticipantRegistration $participantRegistration;
    public readonly ?string $message;

    private function __construct(
        string $participantRegistrationMessage,
        bool $participantRegistrationSuccessful,
        string $businessCardMessage,
        bool $businessCardSuccessful,
        ParticipantRegistration $participantRegistration,
        ?string $message
    ) {
        $this->participantRegistrationMessage = $participantRegistrationMessage;
        $this->participantRegistrationSuccessful = $participantRegistrationSuccessful;
        $this->businessCardMessage = $businessCardMessage;
        $this->businessCardSuccessful = $businessCardSuccessful;
        $this->participantRegistration = $participantRegistration;
        $this->message = $message;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string) ($data['participantRegistrationMessage'] ?? ''),
            (bool) ($data['participantRegistrationSuccessful'] ?? false),
            (string) ($data['businessCardMessage'] ?? ''),
            (bool) ($data['businessCardSuccessful'] ?? false),
            ParticipantRegistration::fromArray((array) ($data['participantRegistration'] ?? [])),
            isset($data['message']) ? (string) $data['message'] : null
        );
    }
}
