<?php

declare(strict_types = 1);

namespace Shopsys\PohodaBundle\Component\JShopsys;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface JShopsysActionCallableInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function call(Request $request, Response $response): Response;
}
