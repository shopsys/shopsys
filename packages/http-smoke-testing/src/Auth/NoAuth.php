<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Auth;

use Override;
use Symfony\Component\HttpFoundation\Request;

class NoAuth implements AuthInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Override]
    public function authenticateRequest(Request $request)
    {
    }
}
