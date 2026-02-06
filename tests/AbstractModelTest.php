<?php

namespace KubernetesRuntime\Tests;

use KubernetesRuntime\Tests\Fixtures\AnotherTestModel;
use KubernetesRuntime\Tests\Fixtures\TestModel;
use KubernetesRuntime\Tests\Fixtures\TestObject;
use KubernetesRuntime\Tests\Fixtures\TestRawModel;
use PHPUnit\Framework\TestCase;

class AbstractModelTest extends TestCase
{

    public function testGetArrayCopy()
    {
        $TestModel = new TestModel([
            'status'           => new TestRawModel(['foo' => 'bar']),
            'testModel'        => new AnotherTestModel(['namespace' => 'test']),
            'metadata'         => [new AnotherTestModel()],
            'testObject'       => new TestObject(),
            'testObjects'      => [new TestObject()],
            '_underscoredName' => 'test'
        ]);
        $data      = $TestModel->getArrayCopy();
        $this->assertArrayHasKey('namespace', $data);
        $this->assertEquals('test-namespace', $data['namespace']);
        $this->assertEquals('test', $data['$underscoredName']);

        $this->assertInstanceOf(TestRawModel::class, $TestModel->status);

        $this->assertIsArray($TestModel->metadata);
        $TestRawModel = $TestModel->metadata[0];
        $this->assertInstanceOf(AnotherTestModel::class, $TestRawModel);
    }

    public function testGetArrayCopyRawObject()
    {
        $TestRawModel = new TestRawModel(['foo' => 'bar']);
        $data         = $TestRawModel->getArrayCopy();
        $this->assertArrayHasKey('foo', $data);
        $this->assertEquals('bar', $data['foo']);

    }

    public function testExchangeArray()
    {
        $TestModel = new AnotherTestModel(['namespace' => 'another']);
        $this->assertEquals('another', $TestModel->namespace);
    }

    public function testExchangeArrayWithString()
    {
        $TestModel = new AnotherTestModel(json_encode(['namespace' => 'another']));
        $this->assertEquals('another', $TestModel->namespace);
    }

    public function testToJson()
    {
        $TestModel = new TestModel;
        $this->assertJsonStringEqualsJsonString(json_encode([
            'namespace'        => 'test-namespace',
            '$underscoredName' => 'test'
        ]),
            $TestModel->toJson());
    }

    public function testIsRawObject()
    {
        $TestRawModel = new TestRawModel();
        $this->assertTrue($TestRawModel->isRawObject());
    }

    public static function provideEmptyArrayHandlingTestData(): iterable
    {
        yield 'empty array for nullable object property (status)' => [
            'input' => ['status' => []],
            'propertyName' => 'status',
            'expectedValue' => null,
        ];

        yield 'empty array for nullable object property (testModel)' => [
            'input' => ['testModel' => []],
            'propertyName' => 'testModel',
            'expectedValue' => null,
        ];

        yield 'empty array for nullable object property (testObject)' => [
            'input' => ['testObject' => []],
            'propertyName' => 'testObject',
            'expectedValue' => null,
        ];

        yield 'missing nullable object property remains null' => [
            'input' => [],
            'propertyName' => 'status',
            'expectedValue' => null,
        ];

        yield 'empty array for collection property remains empty array' => [
            'input' => ['metadata' => []],
            'propertyName' => 'metadata',
            'expectedValue' => [],
        ];
    }

    /**
     * @dataProvider provideEmptyArrayHandlingTestData
     */
    public function testEmptyArrayHandling(array $input, string $propertyName, mixed $expectedValue): void
    {
        $TestModel = new TestModel($input);
        $this->assertEquals($expectedValue, $TestModel->$propertyName);
    }
}
