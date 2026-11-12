<?php

namespace AndyFraussen\Dokapi\Tests;

use AndyFraussen\Dokapi\Exceptions\DokapiException;
use AndyFraussen\Dokapi\Exceptions\DokapiServerException;
use AndyFraussen\Dokapi\Exceptions\DokapiUploadException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class DokapiUploadRecoveryTest extends ApiContractTestCase
{
    #[Test]
    #[DataProvider('documentFlows')]
    public function upload_failures_preserve_created_documents_and_do_not_replay_the_creation(string $flow): void
    {
        $created = ['document' => ['ulid' => '01CREATED'], 'preSignedUploadUrl' => 'https://upload.example.test/object'];
        $client = $this->mockClient([new Response(201, [], json_encode($created)), new Response(503, [], 'Unavailable')]);

        try {
            $flow === 'outgoing' ? $client->sendOutgoingDocument([], '<invoice/>') : $client->sendValidatingDocument('<invoice/>');
            $this->fail('Expected an upload exception.');
        } catch (DokapiUploadException $exception) {
            $this->assertSame($created, $exception->getCreateResponse());
            $this->assertSame('01CREATED', $exception->getDocumentUlid());
            $this->assertSame($created['preSignedUploadUrl'], $exception->getUploadUrl());
            $this->assertInstanceOf(DokapiServerException::class, $exception->getPrevious());
            $this->assertSame(503, $exception->getPrevious()->getStatusCode());
        }

        $this->assertCount(2, $this->history);
        $this->assertSame('POST', $this->history[0]['request']->getMethod());
        $this->assertSame('PUT', $this->history[1]['request']->getMethod());
    }

    public static function documentFlows(): iterable
    {
        yield 'outgoing' => ['outgoing'];
        yield 'validating' => ['validating'];
    }

    #[Test]
    public function network_timeouts_retain_the_created_document_for_reconciliation(): void
    {
        $client = $this->mockClient([
            new Response(201, [], '{"document":{"ulid":"01CREATED"},"preSignedUploadUrl":"https://upload.example.test/object"}'),
            new ConnectException('Timeout', new Request('PUT', 'https://upload.example.test/object')),
        ]);

        try {
            $client->sendOutgoingDocument([], '<invoice/>');
            $this->fail('Expected an upload exception.');
        } catch (DokapiUploadException $exception) {
            $this->assertSame('01CREATED', $exception->getDocumentUlid());
            $this->assertInstanceOf(ConnectException::class, $exception->getPrevious()->getPrevious());
        }
        $this->assertCount(2, $this->history);
    }

    #[Test]
    #[DataProvider('missingUploadUrls')]
    public function missing_or_invalid_upload_urls_also_preserve_the_creation_context(mixed $url): void
    {
        $created = ['document' => ['ulid' => '01CREATED'], 'preSignedUploadUrl' => $url];
        $client = $this->mockClient([new Response(201, [], json_encode($created))]);

        try {
            $client->sendOutgoingDocument([], '<invoice/>');
            $this->fail('Expected an upload exception.');
        } catch (DokapiUploadException $exception) {
            $this->assertSame('01CREATED', $exception->getDocumentUlid());
            $this->assertInstanceOf(DokapiException::class, $exception->getPrevious());
        }
        $this->assertCount(1, $this->history);
    }

    public static function missingUploadUrls(): iterable
    {
        yield [null];
        yield [123];
        yield [''];
        yield ['http://upload.example.test/object'];
    }
}
