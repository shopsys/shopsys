<?php

namespace Shopsys\HttpSmokeTesting\Auth;

use Symfony\Component\HttpFoundation\Request;

class NoAuth implements AuthInterface
{
    public function authenticateRequest(Request $request): void
    {
    }
}
