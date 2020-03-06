<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Tests\App\Test\FunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class AuthenticatorTest extends FunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\Authenticator
     * @inject
     */
    private $authenticator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     * @inject
     */
    private $customerUserFacade;

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

        return $request;
    }
}
