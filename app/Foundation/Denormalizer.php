<?php


namespace App\Foundation;


use kuiper\reflection\ReflectionTypeInterface;
use kuiper\reflection\TypeUtils;
use Symfony\Component\Serializer\Serializer;

class Denormalizer
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Denormalizer constructor.
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param mixed $data Data to restore
     * @param ReflectionTypeInterface $type The expected class to instantiate
     * @return mixed
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function denormalize($data, $type)
    {
        if (TypeUtils::isPrimitive($type)
        || (TypeUtils::isArray($type) && TypeUtils::isPrimitive($type->getValueType()))) {
            return TypeUtils::sanitize($type, $data);
        } elseif (TypeUtils::isComposite($type)) {
            throw new \BadMethodCallException("Cannot denormalize composite type");
        } else {
            return $this->serializer->denormalize($data, (string) $type);
        }
    }
}