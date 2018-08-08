<?php

namespace Shopsys\HttpSmokeTesting\Auth;

use Symfony\Component\HttpFoundation\Request;

interface AuthInterface
{
    /**
     * Makes changes to the provided Request object for it to be authenticated.
     */
    public function authenticateRequest(Request $request);
}
