<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\OrderProcessorMiddlewareInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;

class OrderProcessingTest extends TestCase
{
    public function testAllMiddlewaresAreCalledInStack(): void
    {
        $orderProcessingData = $this->createStub(OrderProcessingData::class);

        $nullMiddleware = $this->createMiddlewareInstance($orderProcessingData, 3);

        $orderProcessingStack = new OrderProcessingStack([
            $nullMiddleware,
            $nullMiddleware,
            $nullMiddleware,
        ]);

        $orderProcessingStack->processNext($orderProcessingData);
    }

    public function testProcessingStackIsRewindOnConsecutiveCalls(): void
    {
        $orderProcessingData = $this->createStub(OrderProcessingData::class);

        // the handle method is called 3 times for each process() call
        $nullMiddleware = $this->createMiddlewareInstance($orderProcessingData, 6);

        $orderProcessingStack = new OrderProcessingStack([
            $nullMiddleware,
            $nullMiddleware,
            $nullMiddleware,
        ]);

        $orderProcessor = new OrderProcessor(
            $orderProcessingStack,
            $this->createStub(CurrentCurrencyProvider::class),
            $this->createStub(CurrencyFacade::class),
        );
        $orderInput = $this->createStub(OrderInput::class);
        $orderData = $this->createStub(OrderData::class);

        $orderProcessor->process($orderInput, $orderData);
        $orderProcessor->process($orderInput, $orderData);
    }

    public function createMiddlewareInstance(
        OrderProcessingData $orderProcessingData,
        int $expectedHandleMethodCalls,
    ): OrderProcessorMiddlewareInterface {
        $nullMiddleware = $this->createMock(OrderProcessorMiddlewareInterface::class);
        $nullMiddleware->expects($this->any())->method('handle')
            ->with($this->isInstanceOf(OrderProcessingData::class), $this->isInstanceOf(OrderProcessingStack::class))
            ->willReturnCallback(function (OrderProcessingData $orderProcessingData, OrderProcessingStack $orderProcessingStack) {
                return $orderProcessingStack->processNext($orderProcessingData);
            });

        $nullMiddleware->expects($this->exactly($expectedHandleMethodCalls))
            ->method('handle')
            ->willReturn($orderProcessingData);

        return $nullMiddleware;
    }
}
