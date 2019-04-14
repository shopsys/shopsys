<?php

namespace Shopsys\PohodaBundle\Model\Product\JShopsys;

use Shopsys\PohodaBundle\Component\JShopsys\JShopsysActionCallableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JShopsysProductImportFacade implements JShopsysActionCallableInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function call(Request $request, Response $response): Response
    {
        return $response->setContent('202-INFO-Na serveru jiz neni co generovat.');
    }
}
