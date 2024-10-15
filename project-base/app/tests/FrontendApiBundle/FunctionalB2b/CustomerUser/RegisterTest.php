<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser;

use App\DataFixtures\Demo\CompanyDataFixture;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrontendApiBundle\Component\Constraints\UniqueBillingAddressApi;
use Tests\FrontendApiBundle\Test\GraphQlB2bDomainTestCase;

class RegisterTest extends GraphQlB2bDomainTestCase
{
    /**
     * @see \Tests\FrontendApiBundle\Functional\Customer\User\RegisterTest::testUniqueBillingAddressIsNotValidatedInB2cRegistration()
     */
    public function testUniqueBillingAddressIsValidatedInRegistration(): void
    {
        $existingBillingAddress = $this->getReferenceForDomain(CompanyDataFixture::SHOPSYS_COMPANY, $this->domain->getId(), Customer::class)->getBillingAddress();
        $variables = [
            'email' => 'no-reply123456@shopsys.com',
            'firstName' => 'First',
            'lastName' => 'Last',
            'password' => 'user123',
            'telephone' => '145612314',
            'newsletterSubscription' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'companyCustomer' => true,
            'country' => 'CZ',
            'companyName' => 'Company name',
            'companyNumber' => $existingBillingAddress->getCompanyNumber(),
        ];
        $response = $this->getResponseContentForGql(__DIR__ . '/../../Functional/_graphql/mutation/RegistrationMutation.graphql', $variables);
        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);

        $validations = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(UniqueBillingAddressApi::DUPLICATE_BILLING_ADDRESS, $validations['input'][0]['code']);
    }
}
