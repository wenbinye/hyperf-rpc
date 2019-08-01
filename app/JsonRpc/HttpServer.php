<?php

namespace App\JsonRpc;

use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

class HttpServer extends \Hyperf\JsonRpc\HttpServer
{
    protected function initRequestAndResponse(SwooleRequest $request, SwooleResponse $response): array
    {
        // Initialize PSR-7 Request and Response objects.
        $psr7Request = Psr7Request::loadFromSwooleRequest($request);
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response($response));
        if (! $this->isHealthCheck($psr7Request)) {
            if (strpos($psr7Request->getHeaderLine('content-type'), 'application/json') === false) {
                $psr7Response = $this->responseBuilder->buildErrorResponse($psr7Request, -32700);
            } else {
                // @TODO Optimize the error handling of encode.
                $content = $this->packer->unpack($psr7Request->getBody()->getContents());
                if (! isset($content['jsonrpc'], $content['method'], $content['params'])) {
                    $psr7Response = $this->responseBuilder->buildErrorResponse($psr7Request, -32600);
                }
            }
        }
        $psr7Request = $psr7Request->withUri($psr7Request->getUri()->withPath($this->buildPath($content['method'] ?? '/')))
            ->withParsedBody($content['params'] ?? null)
            ->withAttribute('data', $content ?? [])
            ->withAttribute('request_id', $content['id'] ?? null);
        Context::set(ServerRequestInterface::class, $psr7Request);
        return [$psr7Request, $psr7Response];
    }

    private function buildPath($jsonRpcMethod)
    {
        if (strpos($jsonRpcMethod, '.') !== false) {
            list($class, $method) = explode(".", $jsonRpcMethod);
            $path = str_replace('\\', '/', $class);
            if ($path[0] !== '/') {
                $path = '/' . $path;
            }
            return $path . '/' . $method;
        } else {
            return $jsonRpcMethod;
        }
    }
}
