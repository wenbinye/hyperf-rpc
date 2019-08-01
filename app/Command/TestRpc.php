<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\CalculatorServiceConsumer;
use App\Service\CalculatorServiceInterface;
use App\Service\Integer;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class TestRpc extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject()
     * @var CalculatorServiceInterface
     */
    private $calculator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('test-rpc');
    }

    public function configure()
    {
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        $this->line("Input like:
1 + 2
3^2 + 4^2
3 / 4");
        while (true) {
            $input = $this->ask("input:");
            $result = null;
            if (preg_match('/^(\d+)\s*\+\s*(\d+)$/', $input, $matches)) {
                $result = $this->calculator->add(...array_map('intval', [$matches[1], $matches[2]]));
                $this->info($input . ' = ' . $result);
            } elseif (preg_match('/^(\d+)\^2\s*\+\s*(\d+)\^2/', $input, $matches)) {
                $result = $this->calculator->squareSum(array_map(function ($val) {
                    return Integer::newInstance((int)$val);
                }, [$matches[1], $matches[2]]));
                $this->info($input . ' = ' . $result);
            } elseif (preg_match('/^(\d+)\s*\/\s*(\d+)/', $input, $matches)) {
                try {
                    $result = $this->calculator->divide(...array_map('intval', [$matches[1], $matches[2]]));
                    $this->info($input . ' = ' . $result);
                } catch (\InvalidArgumentException $e) {
                    $this->error(get_class($e) . ": " . $e->getMessage());
                }
            } elseif (strpos($input, 'quit') !== false) {
                break;
            }
        }
    }
}
