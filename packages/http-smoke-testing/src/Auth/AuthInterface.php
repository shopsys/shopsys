<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Auth;

use Symfony\Component\HttpFoundation\Request;

interface AuthInterface
{
    /**
     * Makes changes to the provided Request object for it to be authenticated.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function authenticateRequest(Request $request);
}
