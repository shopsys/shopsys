<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Security;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Tests\App\Test\FunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class AuthenticatorTest extends FunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @inject
     */
    private Authenticator $authenticator;

    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @inject
     */
    private RequestStack $requestStack;

    public function testSessionIdIsChangedAfterLogin(): void
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);
        $mockedRequest = $this->createMockedRequest();

        $beforeLoginSessionId = $mockedRequest->getSession()->getId();

        $this->authenticator->loginUser($customerUser, $mockedRequest);

        $afterLoginSessionId = $mockedRequest->getSession()->getId();

        $this->assertNotSame($beforeLoginSessionId, $afterLoginSessionId);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    private function createMockedRequest(): Request
    {
        $request = new Request();

        $session = new Session(new MockArraySessionStorage());
        $session->setId('abc');

        $request->setSession($session);

        $this->requestStack->push($request);

        return $request;
    }
}
