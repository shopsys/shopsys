<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper\ChangePersonalDataInputProvider;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

class SelfManageCustomerUserTest extends GraphQlB2bDomainWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = CompanyDataFixture::B2B_COMPANY_SELF_MANAGE_USER_EMAIL;

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\OwnerCustomerUserTest::testChangePersonalDataMutation()
     */
    public function testChangePersonalDataMutationIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Customer/User/graphql/ChangePersonalDataMutation.graphql',
            ChangePersonalDataInputProvider::INPUT_ARRAY,
        );

        $this->assertAccessDeniedError($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\OwnerCustomerUserTest::testCustomerUsersQuery()
     */
    public function testCustomerUsersQueryIsNotAllowed(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/CustomerUsersQuery.graphql');

        $this->assertAccessDeniedWarning($response);
    }

    /**
     * @param array $response
     */
    private function assertAccessDeniedError(array $response): void
    {
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame('access-denied', $errors[0]['extensions']['userCode']);
        $this->assertSame(403, $errors[0]['extensions']['code']);
    }

    /**
     * @param array $response
     */
    private function assertAccessDeniedWarning(array $response): void
    {
        // TODO add methods similar to the ones used in assertAccessDeniedError
        $warnings = $response['extensions']['warnings'];
        $this->assertSame('access-denied', $warnings[0]['extensions']['userCode']);
        $this->assertSame(403, $warnings[0]['extensions']['code']);
    }
}
