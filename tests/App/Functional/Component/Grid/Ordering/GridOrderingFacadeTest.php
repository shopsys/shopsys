<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Grid\Ordering;

use Shopsys\FrameworkBundle\Component\Grid\Ordering\GridOrderingFacade;
use stdClass;
use Tests\App\Test\TransactionFunctionalTestCase;

class GridOrderingFacadeTest extends TransactionFunctionalTestCase
{
    public function testSetPositionWrongEntity()
    {
        $gridOrderingFacade = new GridOrderingFacade($this->em);
        $entity = new stdClass();
        $this->expectException(\Shopsys\FrameworkBundle\Component\Grid\Ordering\Exception\EntityIsNotOrderableException::class);
        $gridOrderingFacade->saveOrdering($entity, []);
    }
}
