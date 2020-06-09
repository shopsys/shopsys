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
}
