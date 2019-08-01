<?php


namespace App\Foundation;


use PHPUnit\Framework\MockObject\Invokable;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerFactory
{
    public function __invoke()
    {
        return new Serializer([
            new ExceptionNormalizer(),
            new ObjectNormalizer(),
            new ArrayDenormalizer()
        ]);
    }
}