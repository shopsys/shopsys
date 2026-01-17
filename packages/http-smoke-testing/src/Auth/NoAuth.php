<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Auth;

use Override;
use Symfony\Component\HttpFoundation\Request;

class NoAuth implements AuthInterface
{
    #[Override]
    public function authenticateRequest(Request $request): void
    {
    }
}
