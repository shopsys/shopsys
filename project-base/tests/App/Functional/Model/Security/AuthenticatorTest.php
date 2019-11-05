<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Tests\App\Test\FunctionalTestCase;

class AuthenticatorTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\Authenticator
     * @inject
     */
    private $authenticator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade
     * @inject
     */
    private $customerFacade;

    public function testSessionIdIsChangedAfterLogin(): void
    {
        $user = $this->customerFacade->getUserById(1);
        $mockedRequest = $this->createMockedRequest();

        $beforeLoginSessionId = $mockedRequest->getSession()->getId();

        $this->authenticator->loginUser($user, $mockedRequest);

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

        return $request;
    }
}
