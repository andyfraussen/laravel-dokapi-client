<?php

namespace AndyFraussen\Dokapi\Requests;

final class WebhookRequest implements PayloadInterface
{
    public readonly string $url;
    /** @var string[] */
    public readonly array $events;

    public function __construct(string $url, array $events)
    {
        $this->url = $url;
        $this->events = array_values($events);
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'events' => $this->events,
        ];
    }
}
