<?php


namespace App\Foundation;


use Hyperf\Di\MetadataCollector;
use Hyperf\Di\ReflectionManager;
use kuiper\docReader\DocReader;
use kuiper\reflection\ReflectionTypeInterface;
use PhpParser\Comment\Doc;

class MethodDefinitionCollector extends MetadataCollector
{
    /**
     * @var array
     */
    protected static $container = [];

    /**
     * Get the method definition from metadata container,
     * If the metadata not exist in container, then will
     * parse it and save into container, and then return it.
     * @param string $class
     * @param string $method
     * @return array
     * @throws \ReflectionException
     * @throws \kuiper\reflection\exception\ClassNotFoundException
     */
    public static function getOrParse(string $class, string $method): array
    {
        $key = $class . '::' . $method;
        if (static::has($key)) {
            return static::get($key);
        }
        $reader = new DocReader();

        $reflectionMethod = ReflectionManager::reflectMethod($class, $method);
        $parameterTypes = $reader->getParameterTypes($reflectionMethod);
        $definitions = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $definition = [
                'type' => $parameterTypes[$parameter->getName()],
                'name' => $parameter->getName(),
                'ref' => '',
                'allowsNull' => $parameter->allowsNull(),
            ];
            if ($parameter->isDefaultValueAvailable()) {
                $definition['defaultValue'] = $parameter->getDefaultValue();
            }
            $definitions[] = $definition;
        }
        static::set($key, $definitions);
        return $definitions;
    }

    public static function getReturnType(string $class, string $method): ReflectionTypeInterface
    {
        $key = $class . '::' . $method . '@return';
        if (static::has($key)) {
            return static::get($key);
        }
        $reader = new DocReader();
        $type = $reader->getReturnType(ReflectionManager::reflectClass($class)->getMethod($method));
        static::set($key, $type);
        return $type;
    }
}