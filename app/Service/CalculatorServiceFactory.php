<?php


namespace App\Service;


use App\JsonRpc\ServiceFactory;

class CalculatorServiceFactory
{
    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * CalculatorServiceFactory constructor.
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(ServiceFactory $serviceFactory)
    {
        $this->serviceFactory = $serviceFactory;
    }

    public function __invoke()
    {
        return $this->serviceFactory->createService(CalculatorServiceInterface::class);
    }
}