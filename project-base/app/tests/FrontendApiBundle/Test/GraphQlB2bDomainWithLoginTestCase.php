<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use App\DataFixtures\Demo\CompanyDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserFacade;
use LogicException;

abstract class GraphQlB2bDomainWithLoginTestCase extends CommonGraphQlWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL;

    /**
     * @inject
     */
    protected CustomerUserFacade $customerUserFacade;

    protected function setUp(): void
    {
        parent::setUp();

        $b2bDomain = $this->domain->findFirstB2bDomain();

        if ($b2bDomain === null) {
            $this->markTestSkipped('No B2B domain found');
        }

        $this->domain->switchDomainById($b2bDomain->getId());

        $this->login();
    }

    /**
     * @param array $response
     */
    protected function assertAccessDeniedError(array $response): void
    {
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame('access-denied', $errors[0]['extensions']['userCode']);
        $this->assertSame(403, $errors[0]['extensions']['code']);
    }

    /**
     * @param array $response
     */
    protected function assertAccessDeniedWarning(array $response): void
    {
        $this->assertResponseContainsArrayOfWarnings($response);
        $warnings = $this->getWarningsFromResponse($response);
        $this->assertSame('access-denied', $warnings[0]['extensions']['userCode']);
        $this->assertSame(403, $warnings[0]['extensions']['code']);
    }

    /**
     * @return \App\Model\Customer\User\CustomerUser
     */
    protected function getCustomerUserByDefaultCredentials(): CustomerUser
    {
        $currentCustomerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
            static::DEFAULT_USER_EMAIL,
            $this->domain->getId(),
        );

        if ($currentCustomerUser === null) {
            throw new LogicException(sprintf('No customer user found with "%s" email on domain ID "%d"', static::DEFAULT_USER_EMAIL, $this->domain->getId()));
        }

        return $currentCustomerUser;
    }
}
