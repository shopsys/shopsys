<?php

namespace Tests\FrameworkBundle\Unit\Component\Router\Security;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;

class DummyController
{
    public function withoutProtectionAction()
    {
    }

    public function withProtectionAction()
    {
    }
}
