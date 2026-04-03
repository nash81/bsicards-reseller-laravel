<?php

namespace BSICards\Tests;

use PHPUnit\Framework\TestCase;
use BSICards\BSICardsClient;
use BSICards\APIException;

class BSICardsClientTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        // Mock credentials for testing
        $this->client = new BSICardsClient(
            'test_public_key_12345',
            'test_secret_key_67890'
        );
    }

    public function testClientInitialization()
    {
        $this->assertNotNull($this->client);
        $this->assertEquals('test_public_key_12345', $this->client->getPublicKey());
        $this->assertEquals('test_secret_key_67890', $this->client->getSecretKey());
    }

    public function testSetPublicKey()
    {
        $this->client->setPublicKey('new_public_key');
        $this->assertEquals('new_public_key', $this->client->getPublicKey());
    }

    public function testSetSecretKey()
    {
        $this->client->setSecretKey('new_secret_key');
        $this->assertEquals('new_secret_key', $this->client->getSecretKey());
    }

    public function testClientThrowsExceptionWithoutCredentials()
    {
        $this->expectException(\InvalidArgumentException::class);
        new BSICardsClient(null, null);
    }

    public function testClientInitializesWithBothKeysRequired()
    {
        $this->expectException(\InvalidArgumentException::class);
        new BSICardsClient('public_key_only', null);
    }
}

