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
        firstName,
        lastName,
        email
        telephone
    }
}
        ';

        $jsonExpected = '
{
    "data": {
        "query": {
            "firstName": "Jaromír",
            "lastName": "Jágr",
            "email": "no-reply@shopsys.com",
            "telephone": "605000123"
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
    }) {
        firstName
        lastName,
        telephone,
        email
    }
}';

        $jsonExpected = '
{
    "data": {
        "ChangePersonalData": {
            "firstName": "John",
            "lastName": "Doe",
            "telephone": "123456321",
            "email": "no-reply@shopsys.com"
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
    }) {
    firstName
        lastName,
        telephone,
        email
    }
}';

        $expectedViolationMessages = [
            0 => 'First name cannot be longer than 100 characters',
            1 => 'Last name cannot be longer than 100 characters',
            2 => 'Telephone number cannot be longer than 30 characters',
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
