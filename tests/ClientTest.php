<?php

namespace KubernetesRuntime\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use KubernetesRuntime\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testRequestTriggersNoGuzzleDeprecations(): void
    {
        $mock = new MockHandler([new Response(200, [], '{}')]);
        Client::configure('https://kubernetes.test', ['token' => 'test'], ['handler' => HandlerStack::create($mock)]);

        $deprecations = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$deprecations): bool {
            $deprecations[] = $errstr;

            return true;
        }, E_USER_DEPRECATED);
        try {
            $response = Client::getInstance()->request('get', '/api/v1/namespaces/default/secrets/test');
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $deprecations);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('GET', $mock->getLastRequest()->getMethod());
        self::assertSame('Bearer test', $mock->getLastRequest()->getHeaderLine('Authorization'));
    }
}
