<?php


namespace KubernetesRuntime\Tests\Fixtures;


use KubernetesRuntime\AbstractModel;

class TestModel extends AbstractModel
{
    public $namespace = 'test-namespace';

    /**
     * @var TestRawModel|null
     */
    public $status;

    /**
     * @var AnotherTestModel[]
     */
    public $metadata;

    /**
     * @var TestRawModel[]
     */
    public $rawModels;

    /**
     * @var AnotherTestModel|null
     */
    public $testModel;

    /**
     * @var TestObject|null
     */
    public $testObject;

    /**
     * @var TestObject[]
     */
    public $testObjects;

    /**
     * @var string
     */
    public $_underscoredName = 'test';
}