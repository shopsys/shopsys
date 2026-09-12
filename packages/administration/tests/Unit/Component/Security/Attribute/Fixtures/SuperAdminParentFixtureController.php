<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\Attribute\Fixtures;

use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;

#[SuperAdminOnly]
class SuperAdminParentFixtureController
{
    public function testMethod(): void
    {
    }
}
