<?php

namespace Tests\FrameworkBundle\Unit\Component\Router\Security;

class DummyController
{
    public function withoutProtectionAction(): void
    {
    }

    public function withProtectionAction(): void
    {
    }
}
