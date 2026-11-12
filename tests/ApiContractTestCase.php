<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Clients\DokapiClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;

abstract class ApiContractTestCase extends DokapiTestCase
{
    protected array $history = [];

    protected function mockClient(array $responses, array $config = [], array $httpOptions = []): DokapiClient
    {
        $this->history = [];
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new DokapiClient(array_replace([
            'base_url' => 'https://peppol.example.test/v1',
            'mandate_base_url' => 'https://mandate.example.test/v1',
            'access_token' => 'test-token',
        ], $config), new Client(array_replace($httpOptions, ['handler' => $stack])));
    }

    protected function fixtureJson(string $name): string
    {
        return file_get_contents(__DIR__ . '/Fixtures/' . $name . '.json');
    }

    protected function fixture(string $name): array
    {
        return json_decode($this->fixtureJson($name), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function assertMatchesSchema(mixed $data, string $api, string $schemaName): void
    {
        $snapshot = json_decode($this->fixtureJson($api . '.schema'), false, 512, JSON_THROW_ON_ERROR);
        $schema = (object) [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            '$ref' => '#/components/schemas/' . $schemaName,
            'components' => $snapshot->components,
        ];
        $result = (new Validator())->validate($data, $schema);
        $error = $result->error();

        $this->assertTrue($result->isValid(), $error === null ? '' : json_encode((new ErrorFormatter())->format($error), JSON_PRETTY_PRINT));
    }

    protected function requestJson(int $index = 0): mixed
    {
        return json_decode((string) $this->history[$index]['request']->getBody(), false, 512, JSON_THROW_ON_ERROR);
    }
}
