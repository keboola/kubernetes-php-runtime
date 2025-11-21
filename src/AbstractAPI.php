<?php
/*
 * This file is part of Kubernete Client.
 *
 * (c) Allan Sun <allan.sun@bricre.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KubernetesRuntime;


use Kubernetes\Model\Io\K8s\Apimachinery\Pkg\Apis\Meta\V1\Status;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAPI
{
    const FUNCTION_WRITE = 'write';
    const FUNCTION_READ = 'read';
    const FUNCTION_STATUS = 'status';

    /**
     * @var string
     * Value to be specified in child class
     */
    protected $group;

    /**
     * @var string
     * Value to be specified in child class
     */
    protected $version;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     * Should be either 'apis' or 'api' depend on each api end point
     */
    protected $apiPrefix = 'apis';

    /**
     * @var string
     * Value to be specified in child class
     */
    protected $apiPostfix;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var bool
     * Internal mark to identify if this API has 'write' functions
     */
    protected $isWriteFunctionAvailable = true;

    /**
     * @var bool
     * Internal mark to identify if this API has 'read' functions
     */
    protected $isReadFunctionAvailable = true;

    /**
     * @var bool
     * Internal mark to identify if this API has 'status' functions
     */
    protected $isStatusFunctionAvailable = true;

    /**
     * @var array
     * Merged response types (standard + custom), computed once in constructor
     */
    readonly private array $responseTypes;

    /**
     * AbstractAPI constructor.
     *
     * @param string $namespace
     */
    public function __construct($namespace = 'default')
    {
        $this->client    = Client::getInstance();
        $this->namespace = $namespace;

        // Merge custom response types with standard types at once (custom takes precedence)
        $this->responseTypes = array_merge(ResponseTypes::TYPES, static::getCustomResponseTypes());
    }

    /**
     * Custom response type mappings for child classes (e.g., custom CRDs)
     * Format: ['operationId' => ['statusCode.' => 'ClassName']]
     * Example: ['listAppsKeboolaComV1NamespacedApp' => ['200.' => AppList::class]]
     */
    protected static function getCustomResponseTypes(): array
    {
        return [];
    }

    /**
     * @param ResponseInterface $response
     *
     * @param string            $operationId
     *
     * @return ModelInterface|mixed
     */
    protected function parseResponse($response, $operationId)
    {
        $contents = (string)$response->getBody();

        if (($response->getHeader('content-type')[0] ?? null) === 'application/json') {
            $contents = json_decode($contents, true);
        }

        if (!is_array($contents) || !array_key_exists('kind', $contents)) {
            return $contents;
        }

        if (array_key_exists($operationId, $this->responseTypes) &&
            array_key_exists($response->getStatusCode() . '.', $this->responseTypes[$operationId])) {
            $className = $this->responseTypes[$operationId][$response->getStatusCode() . '.'];

            return new $className($contents);
        }

        if ('Status' == $contents['kind']) {
            return new Status($contents);
        }


        return $contents;
    }

}