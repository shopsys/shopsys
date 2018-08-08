<?php

namespace Tests\FrameworkBundle\Unit\Component\Grid\Ordering;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingService;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

class GridOrderingServiceTest extends TestCase
{
    public function testSetPositionNull(): void
    {
        $gridOrderingService = new GridOrderingService();
        $entity = null;

        $this->expectException(\Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException::class);
        $gridOrderingService->setPosition($entity, 0);
    }

    public function testSetPositionWrongEntity(): void
    {
        $gridOrderingService = new GridOrderingService();
        $entity = new \StdClass();

        $this->expectException(\Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException::class);
        $gridOrderingService->setPosition($entity, 0);
    }

    public function testSetPosition(): void
    {
        $gridOrderingService = new GridOrderingService();
        $position = 1;
        $entityMock = $this->getMockBuilder(OrderableEntityInterface::class)
            ->setMethods(['setPosition'])
            ->getMockForAbstractClass();
        $entityMock->expects($this->once())->method('setPosition')->with($this->equalTo($position));

        $gridOrderingService->setPosition($entityMock, $position);
    }
}
