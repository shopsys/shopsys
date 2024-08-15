<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class CurrentCustomerUserTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    public function testCurrentCustomerUser(): void
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(self::DEFAULT_USER_EMAIL, $this->domain->getId());
        /** @var \App\Model\Customer\DeliveryAddress $defaultDeliveryAddress */
        $defaultDeliveryAddress = $customerUser->getDefaultDeliveryAddress();
        $pricingGroupName = $customerUser->getPricingGroup()->getName();

        $expected = [
            '__typename' => 'CompanyCustomerUser',
            'firstName' => 'Jaromír',
            'lastName' => 'Jágr',
            'email' => 'no-reply@shopsys.com',
            'telephone' => '605000123',
            'newsletterSubscription' => true,
            'street' => 'Hlubinská 10',
            'city' => 'Ostrava',
            'postcode' => '70200',
            'country' => [
                'code' => 'CZ',
            ],
            'pricingGroup' => $pricingGroupName,
            'defaultDeliveryAddress' => [
                'uuid' => $defaultDeliveryAddress->getUuid(),
                'companyName' => 'Rockpoint',
                'street' => 'Rudná 123',
                'city' => 'Ostrava',
                'postcode' => '70030',
                'telephone' => '123456789',
                'country' => [
                    'code' => 'CZ',
                ],
                'firstName' => 'Eva',
                'lastName' => 'Wallicová',
            ],
            'deliveryAddresses' => [
                [
                    'uuid' => $defaultDeliveryAddress->getUuid(),
                    'companyName' => 'Rockpoint',
                    'street' => 'Rudná 123',
                    'city' => 'Ostrava',
                    'postcode' => '70030',
                    'telephone' => '123456789',
                    'country' => [
                        'code' => 'CZ',
                    ],
                    'firstName' => 'Eva',
                    'lastName' => 'Wallicová',
                ],
            ],
            'companyName' => 'Shopsys',
            'companyNumber' => '12345678',
            'companyTaxNumber' => 'CZ65432123',
            'loginInfo' => [
                'loginType' => LoginTypeEnum::WEB,
                'externalId' => null,
            ],
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CurrentCustomerUserQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'currentCustomerUser');

        $this->assertEquals($expected, $data);
    }

    /**
     * @return array{telephone: string, firstName: string, lastName: string, newsletterSubscription: false, street: string, city: string, country: string, postcode: string}
     */
    private function getJohnDoeBaseData(): array
    {
        return [
            'telephone' => '123456321',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'newsletterSubscription' => false,
            'street' => '123 Fake street',
            'city' => 'Springfield',
            'country' => 'CZ',
            'postcode' => '54321',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function assertJohnDoeBaseData(array $data): void
    {
        $this->assertSame('John', $data['firstName']);
        $this->assertSame('Doe', $data['lastName']);
        $this->assertSame('123456321', $data['telephone']);
        $this->assertSame('no-reply@shopsys.com', $data['email']);
        $this->assertSame('123 Fake street', $data['street']);
        $this->assertSame('Springfield', $data['city']);
        $this->assertSame('CZ', $data['country']['code']);
        $this->assertSame('54321', $data['postcode']);
        $this->assertFalse($data['newsletterSubscription']);
    }

    public function testChangePersonalData(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/ChangePersonalDataMutation.graphql',
            $this->getJohnDoeBaseData(),
        );
        $data = $this->getResponseDataForGraphQlType($response, 'ChangePersonalData');

        $this->assertJohnDoeBaseData($data);
    }

    public function testEditCustomerCompany(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/ChangePersonalDataMutation.graphql',
            [
                ...$this->getJohnDoeBaseData(),
                'companyCustomer' => true,
                'companyName' => 'AirLocks inc.',
                'companyNumber' => '98765432',
                'companyTaxNumber' => 'AL987654321',
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'ChangePersonalData');

        $this->assertJohnDoeBaseData($data);
        $this->assertSame('AirLocks inc.', $data['companyName']);
        $this->assertSame('98765432', $data['companyNumber']);
        $this->assertSame('AL987654321', $data['companyTaxNumber']);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CurrentCustomerUserQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'currentCustomerUser');

        $this->assertJohnDoeBaseData($data);
        $this->assertSame('AirLocks inc.', $data['companyName']);
        $this->assertSame('98765432', $data['companyNumber']);
        $this->assertSame('AL987654321', $data['companyTaxNumber']);
    }

    public function testChangePersonalDataWithWrongData(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/ChangePersonalDataMutation.graphql',
            [
                'telephone' => '1234567890123456789012345678901',
                'firstName' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent suscipit ultrices molestie. Donec s',
                'lastName' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent suscipit ultrices molestie. Donec s',
                'newsletterSubscription' => false,
                'street' => '123 Fake street',
                'city' => 'Springfield',
                'country' => 'CZ',
                'postcode' => '54321',
            ],
        );

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedViolationMessages = [
            0 => t(
                'First name cannot be longer than {{ limit }} characters',
                ['{{ limit }}' => 100],
                'validators',
                $firstDomainLocale,
            ),
            1 => t(
                'Last name cannot be longer than {{ limit }} characters',
                ['{{ limit }}' => 100],
                'validators',
                $firstDomainLocale,
            ),
            2 => t(
                'Telephone number cannot be longer than {{ limit }} characters',
                ['{{ limit }}' => 30],
                'validators',
                $firstDomainLocale,
            ),
        ];

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $responseData = $this->getErrorsExtensionValidationFromResponse($response);

        $i = 0;

        foreach ($responseData as $responseRow) {
            foreach ($responseRow as $validationError) {
                $this->assertArrayHasKey('message', $validationError);
                $this->assertEquals($expectedViolationMessages[$i], $validationError['message']);
                $i++;
            }
        }
    }

    public function testChangePersonalDataWithWrongCompanyData(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/ChangePersonalDataMutation.graphql',
            [
                ...$this->getJohnDoeBaseData(),
                'companyCustomer' => true,
                'companyName' => '  ',
                'companyNumber' => '9876543210123212313212321321321312313123213213131231321321323',
                'companyTaxNumber' => '123',
            ],
        );

        $expectedViolationCodes = [
            0 => NotBlank::IS_BLANK_ERROR,
            1 => Length::TOO_LONG_ERROR,
            2 => Regex::REGEX_FAILED_ERROR,
        ];

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $responseData = $this->getErrorsExtensionValidationFromResponse($response);

        $i = 0;

        foreach ($responseData as $responseRow) {
            foreach ($responseRow as $validationError) {
                $this->assertArrayHasKey('code', $validationError);
                $this->assertEquals($expectedViolationCodes[$i], $validationError['code']);
                $i++;
            }
        }
    }
}
