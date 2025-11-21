<?php

namespace KubernetesRuntime\Tests\Fixtures;

use KubernetesRuntime\AbstractModel;

/**
 * Test model for custom CRD
 */
class CustomCrdModel extends AbstractModel
{
    public string|null $kind = null;
    public string|null $apiVersion = null;
    public array|null $metadata = null;
}
