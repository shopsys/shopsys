<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Login;

use App\DataFixtures\Demo\AdministratorDataFixture;
use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\Model\Administrator\Administrator;
use App\Model\Customer\User\CustomerUser;
use Ramsey\Uuid\Uuid;
use Shopsys\FrontendApiBundle\Model\Security\LoginAsUserFacade;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUserFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\App\Test\FunctionalTestCase;

final class LoginAsUserFacadeTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private LoginAsUserFacade $loginAsUserFacade;

    /**
     * @inject
     */
    private TokenFacade $tokenFacade;

    /**
     * @inject
     */
    private FrontendApiUserFactory $frontendApiUserFactory;

    /**
     * @inject
     */
    private TokenStorageInterface $tokenStorage;

    public function testAdministratorLoggedAsCustomerIsReadFromAuthenticatedUser(): void
    {
        $administrator = $this->getReference(AdministratorDataFixture::SUPERADMINISTRATOR, Administrator::class);
        $this->authenticateCustomerUser($administrator);

        $this->assertSame($administrator->getId(), $this->loginAsUserFacade->getCurrentAdministratorLoggedAsCustomer()?->getId());
        $this->assertTrue($this->loginAsUserFacade->isAdministratorLoggedAsCustomer());
    }

    public function testCustomerUserLoggedInWithoutAdministratorHasNoAdministrator(): void
    {
        $this->authenticateCustomerUser(null);

        $this->assertNull($this->loginAsUserFacade->getCurrentAdministratorLoggedAsCustomer());
        $this->assertFalse($this->loginAsUserFacade->isAdministratorLoggedAsCustomer());
    }

    private function authenticateCustomerUser(?Administrator $administrator): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . '1', CustomerUser::class);
        $accessToken = $this->tokenFacade->createAccessTokenAsString($customerUser, Uuid::uuid4()->toString(), $administrator);
        $frontendApiUser = $this->frontendApiUserFactory->createFromToken($this->tokenFacade->getTokenByString($accessToken));

        $this->tokenStorage->setToken(new UsernamePasswordToken($frontendApiUser, 'frontend_api', $frontendApiUser->getRoles()));
    }
}
