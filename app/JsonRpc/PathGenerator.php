<?php


namespace App\JsonRpc;


use Hyperf\Rpc\Contract\PathGeneratorInterface;

class PathGenerator implements PathGeneratorInterface
{

    public function generate(string $service, string $method): string
    {
        $path = str_replace("\\", "/", $service);
        if ($path[0] != '/') {
            $path = '/' . $path;
        }
        return $path . '/' . $method;
    }
}