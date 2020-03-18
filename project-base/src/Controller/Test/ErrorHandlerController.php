<?php

declare(strict_types=1);

namespace App\Controller\Test;

use App\Controller\Front\FrontBaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorHandlerController extends FrontBaseController
{
    /**
     * @Route("/error-handler/notice")
     */
    public function noticeAction()
    {
        $undefined[42];

        return new Response('');
    }

    /**
     * @Route("/error-handler/exception")
     */
    public function exceptionAction()
    {
        throw new \App\Controller\Test\ExpectedTestException('Expected exception');
    }

    /**
     * @Route("/error-handler/unknown-domain")
     */
    public function unknownDomainAction()
    {
        throw new \Shopsys\FrameworkBundle\Component\Domain\Exception\UnableToResolveDomainException('http://localhost:8000');
    }
}
