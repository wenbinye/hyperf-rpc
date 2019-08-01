<?php


namespace App\JsonRpc;


use App\Foundation\Denormalizer;
use App\Foundation\MethodDefinitionCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\JsonRpc\HttpCoreMiddleware;
use kuiper\reflection\ReflectionTypeInterface;
use kuiper\reflection\TypeUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;

class CoreMiddleware extends HttpCoreMiddleware
{
    /**
     * @var Denormalizer
     */
    protected $denormalizer;

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(ContainerInterface $container, string $serverName)
    {
        parent::__construct($container, $serverName);
        $this->denormalizer = $this->container->get(Denormalizer::class);
        $this->serializer = $this->container->get(Serializer::class);
    }

    protected function handleFound(array $routes, ServerRequestInterface $request)
    {
        try {
            return parent::handleFound($routes, $request);
        } catch (\Exception $e) {
            $body = new SwooleStream($this->packer->pack($this->dataFormatter->formatErrorResponse(
                [$request->getAttribute('request_id') ?? '', 0, 'Server error', [
                    'exception_class' => get_class($e),
                    'exception_data' => $this->serializer->normalize($e)
                ]]
            )));
            return $this->response()->withAddedHeader('content-type', 'application/json')->withBody($body);
        }
    }

    protected function parseParameters(string $controller, string $action, array $arguments): array
    {
        $injections = [];
        $definitions = MethodDefinitionCollector::getOrParse($controller, $action);
        foreach ($definitions ?? [] as $pos => $definition) {
            if (! is_array($definition)) {
                throw new \RuntimeException('Invalid method definition.');
            }
            if (! isset($definition['type']) || ! isset($definition['name'])) {
                $injections[] = null;
                continue;
            }
            $injections[] = value(function () use ($definition, $pos, $arguments) {
                /** @var ReflectionTypeInterface $type */
                $type = $definition['type'];
                $value = $arguments[$pos] ?? $arguments[$definition['name']] ?? null;
                if (!isset($value)) {
                    if (isset($definition['defaultValue'])) {
                        return $definition['defaultValue'];
                    }
                    if (!empty($definition['allowsNull'])) {
                        return null;
                    }
                    throw new \RuntimeException("Invalid method definition detected.");
                }
                return $this->denormalizer->denormalize($value, $type);
            });
        }

        return $injections;
    }
}