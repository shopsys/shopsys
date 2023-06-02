<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\Model\Customer\User\CustomerUserFacade;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class CurrentCustomerUserTest extends GraphQlWithLoginTestCase
{
    /**
     * @var \App\Model\Customer\User\CustomerUserFacade
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    public function testCurrentCustomerUser(): void
    {
        $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(self::DEFAULT_USER_EMAIL, $this->domain->getId());
        /** @var \App\Model\Customer\DeliveryAddress $defaultDeliveryAddress */
        $defaultDeliveryAddress = $customerUser->getDefaultDeliveryAddress();
        $pricingGroupName = $customerUser->getPricingGroup()->getName();

        $query = '
{
    query: currentCustomerUser {
        __typename
        firstName
        lastName
        email
        telephone
        newsletterSubscription
        street
        city
        postcode
        country {
            code
        }
        pricingGroup
        defaultDeliveryAddress {
            uuid
            companyName
            street
            city
            postcode
            telephone
            country {
                code
            }
            firstName
            lastName
        }
        deliveryAddresses {
            uuid
            companyName
            street
            city
            postcode
            telephone
            country {
                code
            }
            firstName
            lastName
        }
        ... on CompanyCustomerUser {
            companyName
            companyNumber
            companyTaxNumber
        }
    }
}
        ';

        $jsonExpected = '
{
    "data": {
        "query": {
            "__typename": "CompanyCustomerUser",
            "firstName": "Jaromír",
            "lastName": "Jágr",
            "email": "no-reply@shopsys.com",
            "telephone": "605000123",
            "newsletterSubscription": true,
            "street": "Hlubinská 10",
            "city": "Ostrava",
            "postcode": "70200",
            "country": {
                "code": "CZ"
            },
            "pricingGroup" : "' . $pricingGroupName . '",
            "defaultDeliveryAddress": {
                "uuid": "' . $defaultDeliveryAddress->getUuid() . '",
                "companyName": "Rockpoint",
                "street": "Rudná 123",
                "city": "Ostrava",
                "postcode": "70030",
                "telephone": "123456789",
                "country": {
                    "code": "CZ"
                },
                "firstName": "Eva",
                "lastName": "Wallicová"
            },
            "deliveryAddresses": [
                {
                    "uuid": "' . $defaultDeliveryAddress->getUuid() . '",
                    "companyName": "Rockpoint",
                    "street": "Rudná 123",
                    "city": "Ostrava",
                    "postcode": "70030",
                    "telephone": "123456789",
                    "country": {
                        "code": "CZ"
                    },
                    "firstName": "Eva",
                    "lastName": "Wallicová"
                }
            ],
            "companyName": "Shopsys",
            "companyNumber": "12345678",
            "companyTaxNumber": "CZ65432123"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testChangePersonalData(): void
    {
        $query = '
mutation {
    ChangePersonalData(input: {
        telephone: "123456321"
        firstName: "John"
        lastName: "Doe"
        newsletterSubscription: false
        street: "123 Fake street"
        city: "Springfield"
        country: "CZ"
        postcode: "54321"
    }) {
        firstName
        lastName,
        telephone,
        email
        street
        city
        country {
            code
        }
        postcode
    }
}';

        $jsonExpected = '
{
    "data": {
        "ChangePersonalData": {
            "firstName": "John",
            "lastName": "Doe",
            "telephone": "123456321",
            "email": "no-reply@shopsys.com",
            "street": "123 Fake street",
            "city": "Springfield",
            "country": {
                "code": "CZ"
            },
            "postcode": "54321"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testEditCustomerCompany(): void
    {
        $mutation = 'mutation {
            ChangePersonalData(input: {
                telephone: "123456321"
                firstName: "John"
                lastName: "Doe"
                newsletterSubscription: false
                street: "123 Fake street"
                city: "Springfield"
                country: "CZ"
                postcode: "54321"
                companyCustomer: true
                companyName: "AirLocks inc."
                companyNumber: "98765432"
                companyTaxNumber: "AL987654321"
            }) {
                firstName
                lastName
                telephone
                email
                street
                city
                country {
                    code
                }
                postcode
                ...on CompanyCustomerUser {
                    companyName
                    companyNumber
                    companyTaxNumber
                }
            }
        }';

        $jsonExpectedTemplate = '{
            "data": {
                "%s": {
                    "firstName": "John",
                    "lastName": "Doe",
                    "telephone": "123456321",
                    "email": "no-reply@shopsys.com",
                    "street": "123 Fake street",
                    "city": "Springfield",
                    "country": {
                        "code": "CZ"
                    },
                    "postcode": "54321",
                    "companyName" : "AirLocks inc.",
                    "companyNumber" : "98765432",
                    "companyTaxNumber" : "AL987654321"
                }
            }
        }';

        $this->assertQueryWithExpectedJson($mutation, sprintf($jsonExpectedTemplate, 'ChangePersonalData'));

        $query = '{
            currentCustomerUser {
                firstName
                lastName
                telephone
                email
                street
                city
                country {
                    code
                }
                postcode
                ...on CompanyCustomerUser {
                    companyName
                    companyNumber
                    companyTaxNumber
                }
            }
        }';

        $this->assertQueryWithExpectedJson($query, sprintf($jsonExpectedTemplate, 'currentCustomerUser'));
    }

    public function testChangePersonalDataWithWrongData(): void
    {
        $query = '
mutation {
    ChangePersonalData(input: {
        telephone: "1234567890123456789012345678901"
        firstName: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent suscipit ultrices molestie. Donec s"
        lastName: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent suscipit ultrices molestie. Donec s"
        newsletterSubscription: false
        street: "123 Fake street"
        city: "Springfield"
        country: "CZ"
        postcode: "54321"
    }) {
    firstName
        lastName,
        telephone,
        email
    }
}';

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

        $response = $this->getResponseContentForQuery($query);
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
        $mutation = 'mutation {
            ChangePersonalData(input: {
                telephone: "123456321"
                firstName: "John"
                lastName: "Doe"
                newsletterSubscription: false
                street: "123 Fake street"
                city: "Springfield"
                country: "CZ"
                postcode: "54321"
                companyCustomer: true
                companyName: "  "
                companyNumber: "9876543210123212313212321321321312313123213213131231321321323"
                companyTaxNumber: "123"
            }) {
                firstName
                lastName
                telephone
                email
                street
                city
                country {
                    code
                }
                postcode
                ...on CompanyCustomerUser {
                    companyName
                    companyNumber
                    companyTaxNumber
                }
            }
        }';

        $expectedViolationCodes = [
            0 => NotBlank::IS_BLANK_ERROR,
            1 => Length::TOO_LONG_ERROR,
            2 => Regex::REGEX_FAILED_ERROR,
        ];

        $response = $this->getResponseContentForQuery($mutation);
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
