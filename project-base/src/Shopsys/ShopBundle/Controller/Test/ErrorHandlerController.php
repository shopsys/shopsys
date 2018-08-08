<?php

namespace Shopsys\ShopBundle\Controller\Test;

use Shopsys\ShopBundle\Controller\Front\FrontBaseController;
use Symfony\Component\HttpFoundation\Response;

class ErrorHandlerController extends FrontBaseController
{
    public function noticeAction()
    {
        $undefined[42];

        return new Response('');
    }

    public function exceptionAction(): void
    {
        throw new \Shopsys\ShopBundle\Controller\Test\ExpectedTestException('Expected exception');
    }
}
