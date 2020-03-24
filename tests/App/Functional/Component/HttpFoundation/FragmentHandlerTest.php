<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\HttpFoundation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class FragmentHandlerTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Symfony\Bridge\Twig\Extension\HttpKernelRuntime
     * @inject
     */
    private $httpKernelRuntime;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     * @inject
     */
    private $requestStack;

    public function testRenderingFragmentDoesNotIgnoreException()
    {
        // Rendering a fragment can only be done when handling a Request.
        $this->putFakeRequestToRequestStack();

        $this->expectException(\App\Controller\Test\ExpectedTestException::class);

        /** This should call @see \Shopsys\FrameworkBundle\Component\HttpFoundation\FragmentHandler::render() */
        $this->httpKernelRuntime->renderFragment('/test/error-handler/exception');
    }

    private function putFakeRequestToRequestStack()
    {
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $this->requestStack->push($request);
    }
}
