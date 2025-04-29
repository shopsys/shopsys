<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class ChangePasswordTest extends GraphQlWithLoginTestCase
{
    public function testChangePassword(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePasswordMutation.graphql', [
            'email' => 'no-reply@shopsys.com',
            'oldPassword' => 'user123',
            'newPassword' => 'user124',
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangePassword');
        $this->assertSame('Jaromír', $responseData['firstName']);
        $this->assertSame('Jágr', $responseData['lastName']);
        $this->assertSame('no-reply@shopsys.com', $responseData['email']);
        $this->assertSame('605000123', $responseData['telephone']);
    }

    public function testChangePasswordWithWrongData(): void
    {
        $expectedViolationMessages = [
            0 => t('New password must be at least {{ limit }} characters long', ['{{ limit }}' => 6], Translator::CUSTOMER_VALIDATOR_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
        ];

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePasswordMutation.graphql', [
            'email' => 'no-reply@shopsys.com',
            'oldPassword' => 'user123',
            'newPassword' => 'user1',
        ]);
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

    public function testChangePasswordWithAnotherUserEmailReturnsAccessDenied(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePasswordMutation.graphql', [
            'email' => 'no-reply.3@shopsys.com',
            'oldPassword' => 'user123',
            'newPassword' => 'user123',
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertSame('access-denied', $errors[0]['extensions']['userCode']);
        $this->assertSame(403, $errors[0]['extensions']['code']);
    }
}
