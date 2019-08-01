<?php


namespace App\JsonRpc;


use ProxyManager\Factory\RemoteObjectFactory;
use Psr\Container\ContainerInterface;

class ServiceFactory
{
    /**
     * @var RemoteObjectFactory
     */
    private $proxyFactory;

    /**
     * ServiceFactory constructor.
     * @param ContainerInterface $container
     * @param RemoteObjectFactory $proxyFactory
     */
    public function __construct(ContainerInterface $container, RemoteObjectFactory $proxyFactory = null)
    {
        $this->proxyFactory = $proxyFactory ?: new RemoteObjectFactory(new ServiceHandler($container));
    }

    public function createService(string $serviceName)
    {
        return $this->proxyFactory->createProxy($serviceName);
    }
}