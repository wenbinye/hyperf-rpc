<?php


namespace App\JsonRpc;


use App\Exception\RpcException;
use App\Foundation\Denormalizer;
use App\Foundation\MethodDefinitionCollector;
use Hyperf\Contract\IdGeneratorInterface;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Serializer\Serializer;

class ServiceClient extends \Hyperf\RpcClient\AbstractServiceClient
{
    /**
     * @var Denormalizer
     */
    private $denormalizer;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(ContainerInterface $container, string $serviceName)
    {
        $this->serviceName = $serviceName;
        parent::__construct($container);
        $this->denormalizer = $container->get(Denormalizer::class);
        $this->serializer = $container->get(Serializer::class);
    }

    public function call(string $method, array $params)
    {
        return $this->__request($method, $params);
    }

    protected function __request(string $method, array $params, ?string $id = null)
    {
        if ($this->idGenerator instanceof IdGeneratorInterface && !$id) {
            $id = $this->idGenerator->generate();
        }
        $response = $this->client->send($this->__generateData($method, $params, $id));
        if (is_array($response)) {
            if (isset($response['result'])) {
                $type = MethodDefinitionCollector::getReturnType($this->serviceName, $method);
                return $this->denormalizer->denormalize($response['result'], $type);
            } elseif (isset($response['error'])) {
                $error = $response['error'];
                if (isset($error['code'])) {
                    if ($error['code'] == 0 && isset($error['data']['exception_class'])) {
                        $data = $error['data'];
                        $e = $this->serializer->denormalize($data['exception_data'] ?? [], $data['exception_class']);
                        if ($e instanceof \Throwable) {
                            throw $e;
                        }
                    }
                    throw new RpcException($error['code'], $error['message'] ?? '');
                }
            }
        }
        throw new RuntimeException('Invalid response.');
    }
}