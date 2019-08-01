<?php


namespace App\Service;


use App\Foundation\MethodDefinitionCollector;
use App\JsonRpc\ServiceClient;
use Psr\Container\ContainerInterface;
use Symfony\Component\Serializer\Serializer;

class CalculatorServiceConsumer extends ServiceClient implements CalculatorServiceInterface
{
    /**
     * 定义对应服务提供者的服务名称
     * @var string
     */
    protected $serviceName = CalculatorServiceInterface::class;

    /**
     * 定义对应服务提供者的服务协议
     * @var string
     */
    protected $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int
    {
        return $this->__request(__FUNCTION__, [$a, $b]);
    }

    public function squareSum(array $a): Integer
    {
        return $this->__request(__FUNCTION__, [$a]);
    }

    public function divide(int $a, int $divider)
    {
        return $this->__request(__FUNCTION__, [$a, $divider]);
    }
}