<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class CurrentCustomerUserTest extends GraphQlWithLoginTestCase
{
    public function testCurrentCustomerUser(): void
    {
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
        country
        ... on CurrentCompanyCustomerUser {
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
            "__typename": "CurrentCompanyCustomerUser",
            "firstName": "Jaromír",
            "lastName": "Jágr",
            "email": "no-reply@shopsys.com",
            "telephone": "605000123",
            "newsletterSubscription": true,
            "street": "Hlubinská 5",
            "city": "Ostrava",
            "postcode": "70200",
            "country": "CZ",
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
        country
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
            "country": "CZ",
            "postcode": "54321"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
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
                ['{{ limit }}' => 30],
                'validators',
                $firstDomainLocale
            ),
            1 => t(
                'Last name cannot be longer than {{ limit }} characters',
                ['{{ limit }}' => 30],
                'validators',
                $firstDomainLocale
            ),
            2 => t(
                'Telephone number cannot be longer than {{ limit }} characters',
                ['{{ limit }}' => 30],
                'validators',
                $firstDomainLocale
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
}
