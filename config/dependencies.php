<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

return [
    'dependencies' => [
        \Symfony\Component\Serializer\Serializer::class => \App\Foundation\SerializerFactory::class,
        /**
         * 支持使用接口名做 RpcService.name，并支持名字空间
         */
        \Hyperf\RpcServer\Router\DispatcherFactory::class => \App\JsonRpc\Router\DispatcherFactory::class,
        \Hyperf\JsonRpc\PathGenerator::class => \App\JsonRpc\PathGenerator::class,
        /**
         * 使用 symfony serializer 做序列化
         */
        \Hyperf\JsonRpc\DataFormatter::class => \App\JsonRpc\DataFormatter::class,
        /**
         * 使用 \App\JsonRpc\CoreMiddleware，重载 parseParameters，支持数组形式参数，使用 symfony serializer
         * 支持使用注解设置复杂参数类型
         */
        \Hyperf\JsonRpc\HttpServer::class => \App\JsonRpc\HttpServerFactory::class,

        \App\Service\CalculatorServiceInterface::class => \App\Service\CalculatorServiceFactory::class,
    ],
];
