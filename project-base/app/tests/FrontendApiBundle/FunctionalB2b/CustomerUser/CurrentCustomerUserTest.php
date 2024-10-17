<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrontendApiBundle\Component\Constraints\UniqueBillingAddressApi;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainWithLoginTestCase;

class CurrentCustomerUserTest extends GraphQlB2bDomainWithLoginTestCase
{
    /**
     * @see \Tests\FrontendApiBundle\Functional\Customer\User\CurrentCustomerUserTest::testUniqueBillingAddressIsNotValidatedInEditCustomerCompanyB2c()
     */
    public function testUniqueBillingAddressIsValidatedInEditCustomerCompany(): void
    {
        $existingBillingAddress = $this->getReferenceForDomain(CompanyDataFixture::SHOPSYS_COMPANY, $this->domain->getId(), Customer::class)->getBillingAddress();

        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../Functional/Customer/User/graphql/ChangePersonalDataMutation.graphql',
            [
                'telephone' => '123456321',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'newsletterSubscription' => false,
                'street' => '123 Fake street',
                'city' => 'Springfield',
                'country' => 'CZ',
                'postcode' => '54321',
                'companyCustomer' => true,
                'companyName' => 'AirLocks inc.',
                'companyNumber' => $existingBillingAddress->getCompanyNumber(),
                'companyTaxNumber' => 'AL987654321',
            ],
        );
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $validations = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(UniqueBillingAddressApi::DUPLICATE_BILLING_ADDRESS, $validations['input'][0]['code']);
    }
}
