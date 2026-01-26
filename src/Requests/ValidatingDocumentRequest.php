<?php

namespace AndyFraussen\Dokapi\Requests;

final class ValidatingDocumentRequest implements PayloadInterface
{
    public readonly ?string $externalReference;

    public function __construct(?string $externalReference = null)
    {
        $this->externalReference = $externalReference;
    }

    public function toArray(): array
    {
        $payload = [];
        if ($this->externalReference !== null) {
            $payload['externalReference'] = $this->externalReference;
        }
        return $payload;
    }
}
