<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\Security;

use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Symfony\Component\HttpFoundation\Response;

final class ControllerProtected
{
    #[CsrfProtection]
    public function __invoke(): Response
    {
        return new Response();
    }
}
