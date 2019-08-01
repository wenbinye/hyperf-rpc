<?php


namespace App\Service;


interface CalculatorServiceInterface
{
    public function add(int $a, int $b): int;

    /**
     * @param Integer[] $a
     * @return Integer
     */
    public function squareSum(array $a): Integer;

    /**
     * @param int $a
     * @param int $divider
     * @return float
     * @throws \InvalidArgumentException
     */
    public function divide(int $a, int $divider);
}