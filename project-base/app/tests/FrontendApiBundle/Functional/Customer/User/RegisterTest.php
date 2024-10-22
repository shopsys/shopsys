<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RegisterTest extends GraphQlTestCase
{
    private const DEFAULT_USER_FIRST_NAME = 'John';
    private const DEFAULT_USER_LAST_NAME = 'Doe';
    private const DEFAULT_USER_EMAIL = 'new-no-reply@shopsys.com';
    private const DEFAULT_USER_PASSWORD = 'user123';

    public function testRegister(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/mutation/RegistrationMutation.graphql', self::getRegisterQueryVariables());

        $this->assertSuccessfulRegistration($response);
    }

    /**
     * @see \Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\RegisterTest::testUniqueBillingAddressIsValidatedInRegistration()
     */
    public function testUniqueBillingAddressIsNotValidatedInB2cRegistration(): void
    {
        if ($this->domain->isB2b()) {
            $this->markTestSkipped('This test is only for B2C domains');
        }

        $existingBillingAddress = $this->getReference(CustomerUserDataFixture::BILLING_ADDRESS_PERSISTENT_REFERENCE, BillingAddress::class);

        $registerQueryVariables = self::getRegisterQueryVariables();
        $registerQueryVariables['companyCustomer'] = true;
        $registerQueryVariables['companyNumber'] = $existingBillingAddress->getCompanyNumber();
        $registerQueryVariables['companyName'] = 'Company name';
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/mutation/RegistrationMutation.graphql', $registerQueryVariables);

        $this->assertSuccessfulRegistration($response);
    }

    public function testCompanyUserRegistration(): void
    {
        $registerQueryVariables = self::getRegisterQueryVariables();
        $registerQueryVariables['companyCustomer'] = true;
        $registerQueryVariables['companyNumber'] = '999';
        $registerQueryVariables['companyName'] = 'Company name';
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/mutation/RegistrationMutation.graphql', $registerQueryVariables);

        $this->assertSuccessfulRegistration($response);
    }

    public function testRegisterAlreadyRegisteredCustomerUser(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/mutation/RegistrationMutation.graphql', self::getRegisterQueryVariables('no-reply@shopsys.com'));

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedViolationMessage = t(
            'This email is already registered',
            [],
            Translator::VALIDATOR_TRANSLATION_DOMAIN,
            $firstDomainLocale,
        );

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $responseData = $this->getErrorsExtensionValidationFromResponse($response);

        $this->assertArrayHasKey('input.email', $responseData);

        $alreadyRegisteredEmailError = $responseData['input.email'][0];

        $this->assertArrayHasKey('message', $alreadyRegisteredEmailError);
        $this->assertEquals($expectedViolationMessage, $alreadyRegisteredEmailError['message']);
    }

    public function testRegisterWithWrongData(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../../_graphql/mutation/RegistrationMutation.graphql',
            self::getRegisterQueryVariables(
                'no-replyshopsys.com',
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent suscipit ultrices molestie. Donec s',
                'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent suscipit ultrices molestie. Donec s',
                '123',
            ),
        );

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $responseData = $this->getErrorsExtensionValidationFromResponse($response);

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedViolationMessages = [
            0 => t(
                'First name cannot be longer than {{ limit }} characters',
                ['{{ limit }}' => 100],
                Translator::VALIDATOR_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            1 => t(
                'Last name cannot be longer than {{ limit }} characters',
                ['{{ limit }}' => 100],
                Translator::VALIDATOR_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            2 => t(
                'Please enter valid email',
                [],
                Translator::VALIDATOR_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
            3 => t(
                'Password must be at least {{ limit }} characters long',
                ['{{ limit }}' => 6],
                Translator::VALIDATOR_TRANSLATION_DOMAIN,
                $firstDomainLocale,
            ),
        ];

        $i = 0;

        foreach ($responseData as $responseRow) {
            foreach ($responseRow as $validationError) {
                $this->assertArrayHasKey('message', $validationError);
                $this->assertEquals($expectedViolationMessages[$i], $validationError['message']);
                $i++;
            }
        }
    }

    /**
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $password
     * @return array
     */
    public static function getRegisterQueryVariables(
        string $email = self::DEFAULT_USER_EMAIL,
        string $firstName = self::DEFAULT_USER_FIRST_NAME,
        string $lastName = self::DEFAULT_USER_LAST_NAME,
        string $password = self::DEFAULT_USER_PASSWORD,
    ): array {
        return [
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'password' => $password,
            'telephone' => '145612314',
            'newsletterSubscription' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'companyCustomer' => false,
            'country' => 'CZ',
        ];
    }

    /**
     * @param array $response
     */
    private function assertSuccessfulRegistration(array $response): void
    {
        $graphQlType = 'Register';
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey('tokens', $responseData);
        $this->assertIsString($responseData['tokens']['accessToken']);

        $this->assertArrayHasKey('tokens', $responseData);
        $this->assertIsString($responseData['tokens']['refreshToken']);
    }
}
