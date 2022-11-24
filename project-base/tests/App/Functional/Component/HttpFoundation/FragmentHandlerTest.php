<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\HttpFoundation;

use App\Controller\Test\ExpectedTestException;
use Symfony\Bridge\Twig\Extension\HttpKernelRuntime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
    private HttpKernelRuntime $httpKernelRuntime;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     * @inject
     */
    private RequestStack $requestStack;

    public function testRenderingFragmentDoesNotIgnoreException(): void
    {
        // Rendering a fragment can only be done when handling a Request.
        $this->putFakeRequestToRequestStack();

        $this->expectException(ExpectedTestException::class);

        /** This should call @see \Shopsys\FrameworkBundle\Component\HttpFoundation\FragmentHandler::render() */
        $this->httpKernelRuntime->renderFragment('/test/error-handler/exception');
    }

    private function putFakeRequestToRequestStack(): void
    {
        $request = new Request();
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $this->requestStack->push($request);
    }
}
