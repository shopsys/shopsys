<?php

namespace Tests\FrameworkBundle\Unit\Component\Router\Security;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Symfony\Component\HttpFoundation\Response;

final class ControllerProtected
{
    /**
     * @CsrfProtection
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        return new Response();
    }
}
