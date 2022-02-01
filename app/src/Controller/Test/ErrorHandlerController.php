<?php

declare(strict_types=1);

namespace App\Controller\Test;

use Shopsys\FrameworkBundle\Component\Domain\Exception\UnableToResolveDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorHandlerController extends AbstractController
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
        throw new ExpectedTestException('Expected exception');
    }

    /**
     * @Route("/error-handler/unknown-domain")
     */
    public function unknownDomainAction()
    {
        throw new UnableToResolveDomainException('http://localhost:8000');
    }
}
