<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Router\Security;

use Symfony\Component\HttpFoundation\Response;

final class ControllerNotProtected
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        return new Response();
    }
}
