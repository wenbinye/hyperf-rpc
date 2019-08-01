<?php


namespace App\JsonRpc;

use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\RequestDispatcher;
use Psr\Container\ContainerInterface;

class HttpServerFactory
{
    protected $coreMiddleware = CoreMiddleware::class;

    public function __invoke(ContainerInterface $container): HttpServer
    {
        $dispatcher = $container->get(RequestDispatcher::class);
        $protocolManager = $container->get(ProtocolManager::class);
        return new HttpServer('jsonrpc-http', $this->coreMiddleware, $container, $dispatcher, $protocolManager);
    }
}
