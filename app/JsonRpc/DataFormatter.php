<?php


namespace App\JsonRpc;


use Symfony\Component\Serializer\Serializer;

class DataFormatter extends \Hyperf\JsonRpc\DataFormatter
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * DataFormatter constructor.
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function formatRequest($data)
    {
        $data[1] = $this->serializer->normalize($data[1]);
        return parent::formatRequest($data);
    }

    public function formatResponse($data)
    {
        [$id, $result] = $data;
        $result = $this->serializer->normalize($result);
        return parent::formatResponse([$id, $result]);
    }
}