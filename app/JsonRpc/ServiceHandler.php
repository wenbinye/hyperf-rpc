<?php


namespace App\JsonRpc;


use Hyperf\Utils\Traits\Container;
use ProxyManager\Factory\RemoteObject\AdapterInterface;
use Psr\Container\ContainerInterface;

class ServiceHandler implements AdapterInterface
{
    use Container;

    /**
     * @var ContainerInterface
     */
    private $di;

    /**
     * ServiceHandler constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->di = $container;
    }

    /**
     * Call remote object
     *
     * @param string $wrappedClass
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function call(string $wrappedClass, string $method, array $params = [])
    {
        if (!self::has($wrappedClass)) {
            self::set($wrappedClass, new ServiceClient($this->di, $wrappedClass));
        }
        return self::get($wrappedClass)->call($method, $params);
    }
}