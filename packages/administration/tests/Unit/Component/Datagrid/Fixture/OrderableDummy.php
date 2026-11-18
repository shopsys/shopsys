<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture;

use Override;
use Shopsys\FrameworkBundle\Component\Grid\Ordering\OrderableEntityInterface;

final class OrderableDummy implements OrderableEntityInterface
{
    #[Override]
    public function setPosition(int $position): void
    {
    }
}
