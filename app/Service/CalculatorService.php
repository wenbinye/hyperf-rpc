<?php


namespace App\Service;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * @RpcService(name=CalculatorServiceInterface::class)
 */
class CalculatorService implements CalculatorServiceInterface
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    /**
     * {@inheritDoc}
     */
    public function squareSum(array $a): Integer
    {
        $result = array_reduce($a, function($result, Integer $value) {
            return $result + $value->getValue() ** 2;
        });
        return Integer::newInstance($result);
    }

    /**
     * {@inheritDoc}
     */
    public function divide(int $a, int $divider)
    {
        if ($divider === 0) {
            throw new \InvalidArgumentException("Expected non-zero value for divider");
        }
        return $a/$divider;
    }
}

