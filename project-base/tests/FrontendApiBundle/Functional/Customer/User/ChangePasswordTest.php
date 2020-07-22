<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class ChangePasswordTest extends GraphQlWithLoginTestCase
{
    public function testChangePassword(): void
    {
        $query = '
mutation {
    ChangePassword(input: {
        email: "no-reply@shopsys.com"
        oldPassword: "user123"
        newPassword: "user124"
    }) {
        firstName
        lastName
        email
        telephone
    }
}';

        $jsonExpected = '
{
    "data": {
        "ChangePassword": {
            "firstName": "Jaromír",
            "lastName": "Jágr",
            "email": "no-reply@shopsys.com",
            "telephone": "605000123"
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testChangePasswordWithWrongData(): void
    {
        $query = '
mutation {
    ChangePassword(input: {
        email: "no-reply@shopsys.com"
        oldPassword: "user123"
        newPassword: "user1"
    }) {
        firstName
        lastName
        email
        telephone
    }
}';
        $expectedViolationMessages = [
            0 => 'New password must be at least 6 characters long',
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
