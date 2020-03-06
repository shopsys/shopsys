<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Doctrine;

use App\Model\Order\Order;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Component\Doctrine\GroupedScalarHydrator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Tests\App\Test\TransactionFunctionalTestCase;

class GroupedScalarHydratorTest extends TransactionFunctionalTestCase
{
    public function testHydrateAllData()
    {
        $qb = $this->em->createQueryBuilder()
            ->select('o, oi')
            ->from(Order::class, 'o')
            ->join(OrderItem::class, 'oi', Join::WITH, 'oi.order = o')
            ->setMaxResults(1);

        $rows = $qb->getQuery()->execute(null, GroupedScalarHydrator::HYDRATION_MODE);
        $row = $rows[0];

        $this->assertIsArray($row);

        $this->assertCount(2, $row);
        $this->assertArrayHasKey('o', $row);
        $this->assertArrayHasKey('oi', $row);

        $this->assertIsArray($row['o']);
        $this->assertIsArray($row['oi']);

        $this->assertArrayHasKey('id', $row['o']);
        $this->assertArrayHasKey('id', $row['oi']);
    }
}
