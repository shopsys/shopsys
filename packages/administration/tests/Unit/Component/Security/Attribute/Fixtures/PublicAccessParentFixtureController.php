<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\Attribute\Fixtures;

use Shopsys\FrameworkBundle\Component\Security\Attribute\PublicAccess;

#[PublicAccess]
class PublicAccessParentFixtureController
{
    public function testMethod(): void
    {
    }
}
