<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Symfony\Component\Validator\Constraints\Email;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class IsCustomerUserRegisteredTest extends GraphQlTestCase
{
    public function testIsUserRegisteredWithRegisteredEmail(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/IsCustomerUserRegisteredQuery.graphql', [
            'email' => 'no-reply@shopsys.com',
        ]);

        $this->assertTrue($response['data']['isCustomerUserRegistered']);
    }

    public function testIsUserRegisteredWithNotRegisteredEmail(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/IsCustomerUserRegisteredQuery.graphql', [
            'email' => 'not-registered@shopsys.com',
        ]);

        $this->assertFalse($response['data']['isCustomerUserRegistered']);
    }

    public function testIsUserRegisteredWithInvalidEmail(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/IsCustomerUserRegisteredQuery.graphql', [
            'email' => 'invalidEmail',
        ]);

        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);

        $this->assertSame(Email::INVALID_FORMAT_ERROR, $validationErrors['email'][0]['code']);
    }
}
