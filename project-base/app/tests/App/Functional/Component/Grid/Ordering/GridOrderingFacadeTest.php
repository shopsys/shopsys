<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Grid\Ordering;

use Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingFacade;
use stdClass;
use Tests\App\Test\TransactionFunctionalTestCase;

class GridOrderingFacadeTest extends TransactionFunctionalTestCase
{
    public function testSetPositionWrongEntity(): void
    {
        $gridOrderingFacade = new GridOrderingFacade($this->em);
        $entity = new stdClass();
        $this->expectException(EntityIsNotOrderableException::class);
        /** @phpstan-ignore-next-line */
        $gridOrderingFacade->saveOrdering($entity, []);
    }
}
