<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrontendApiBundle\Component\Constraints\UniqueBillingAddressApi;
use Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper\ChangePersonalDataInputProvider;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

class OwnerCustomerUserTest extends GraphQlB2bDomainWithLoginTestCase
{
    /**
     * @see \Tests\FrontendApiBundle\Functional\Customer\User\CurrentCustomerUserTest::testUniqueBillingAddressIsNotValidatedInEditCustomerCompanyB2c()
     */
    public function testUniqueBillingAddressIsValidatedInEditCustomerCompany(): void
    {
        $existingBillingAddress = $this->getReferenceForDomain(CompanyDataFixture::SHOPSYS_COMPANY, $this->domain->getId(), Customer::class)->getBillingAddress();

        $input = ChangePersonalDataInputProvider::INPUT_ARRAY;
        $input['companyNumber'] = $existingBillingAddress->getCompanyNumber();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Customer/User/graphql/ChangePersonalDataMutation.graphql',
            $input,
        );
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $validations = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(UniqueBillingAddressApi::DUPLICATE_BILLING_ADDRESS, $validations['input'][0]['code']);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\SelfManageCustomerUserTest::testChangePersonalDataMutationIsNotAllowed()
     */
    public function testChangePersonalDataMutation(): void
    {
        $personalData = ChangePersonalDataInputProvider::INPUT_ARRAY;
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Customer/User/graphql/ChangePersonalDataMutation.graphql',
            $personalData,
        );
        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangePersonalData');

        $this->assertSame($personalData['telephone'], $responseData['telephone']);
        $this->assertSame($personalData['firstName'], $responseData['firstName']);
        $this->assertSame($personalData['lastName'], $responseData['lastName']);
        $this->assertSame($personalData['newsletterSubscription'], $responseData['newsletterSubscription']);
        $this->assertSame($personalData['street'], $responseData['street']);
        $this->assertSame($personalData['country'], $responseData['country']['code']);
        $this->assertSame($personalData['postcode'], $responseData['postcode']);
        $this->assertSame($personalData['companyName'], $responseData['companyName']);
        $this->assertSame($personalData['companyNumber'], $responseData['companyNumber']);
        $this->assertSame($personalData['companyTaxNumber'], $responseData['companyTaxNumber']);
    }

    public function testCustomerUsersQuery(): void
    {
        $expectedData = [
            ['email' => CompanyDataFixture::B2B_COMPANY_SELF_MANAGE_USER_EMAIL],
            ['email' => CompanyDataFixture::B2B_COMPANY_LIMITED_USER_EMAIL],
            ['email' => CompanyDataFixture::B2B_COMPANY_OWNER_EMAIL],
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/CustomerUsersQuery.graphql');

        $responseData = $this->getResponseDataForGraphQlType($response, 'customerUsers');

        $this->assertSame($expectedData, $responseData);
    }
}
