<?php

namespace KubernetesRuntime\Tests;

use GuzzleHttp\Psr7\Response;
use KubernetesRuntime\AbstractAPI;
use KubernetesRuntime\Client;
use KubernetesRuntime\Tests\Fixtures\CustomCrdModel;
use PHPUnit\Framework\TestCase;

class AbstractAPITest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configure Client for testing
        Client::configure('https://kubernetes.example.com', [
            'username' => 'test',
            'password' => 'test',
        ]);
    }

    public function testParseResponseWithCustomResponseTypes(): void
    {
        // Create a test API class with custom response types
        $api = new class('test-namespace') extends AbstractAPI {
            protected static function getCustomResponseTypes(): array
            {
                return [
                    'testOperation' => [
                        '200.' => CustomCrdModel::class,
                    ],
                ];
            }

            public function testParseResponse($response, $operationId)
            {
                return $this->parseResponse($response, $operationId);
            }
        };

        // Create a mock response with JSON body
        $responseBody = json_encode([
            'kind' => 'TestCustom',
            'apiVersion' => 'v1',
            'metadata' => ['name' => 'test-resource'],
        ]);

        $response = new Response(200, ['content-type' => 'application/json'], $responseBody);

        // Test that custom response type is used
        $result = $api->testParseResponse($response, 'testOperation');

        $this->assertInstanceOf(CustomCrdModel::class, $result);
        $this->assertEquals('TestCustom', $result->kind);
        $this->assertEquals('v1', $result->apiVersion);
    }

    public function testParseResponseWithoutCustomResponseTypes(): void
    {
        // Create a test API class without custom response types
        $api = new class('test-namespace') extends AbstractAPI {
            public function testParseResponse($response, $operationId)
            {
                return $this->parseResponse($response, $operationId);
            }
        };

        // Create a mock response for unknown operation
        $responseBody = json_encode([
            'kind' => 'UnknownResource',
            'apiVersion' => 'v1',
            'metadata' => ['name' => 'test'],
        ]);

        $response = new Response(200, ['content-type' => 'application/json'], $responseBody);

        // Test that raw array is returned when no mapping exists
        $result = $api->testParseResponse($response, 'unknownOperation');

        $this->assertIsArray($result);
        $this->assertEquals('UnknownResource', $result['kind']);
    }

    public function testCustomResponseTypesTakePrecedenceOverStandardTypes(): void
    {
        // Create a test API class that overrides a standard operation
        $api = new class('test-namespace') extends AbstractAPI {
            protected static function getCustomResponseTypes(): array
            {
                return [
                    'getCoreAPIVersions' => [
                        '200.' => CustomCrdModel::class,
                    ],
                ];
            }

            public function testParseResponse($response, $operationId)
            {
                return $this->parseResponse($response, $operationId);
            }
        };

        $responseBody = json_encode([
            'kind' => 'Custom',
            'apiVersion' => 'v1',
        ]);

        $response = new Response(200, ['content-type' => 'application/json'], $responseBody);

        // Test that custom type takes precedence
        $result = $api->testParseResponse($response, 'getCoreAPIVersions');

        $this->assertInstanceOf(CustomCrdModel::class, $result);
    }
}
