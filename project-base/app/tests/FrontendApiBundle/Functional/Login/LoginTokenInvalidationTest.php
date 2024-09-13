<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Login;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class LoginTokenInvalidationTest extends GraphQlTestCase
{
    public function testAccessTokenIsNotValidAfterPasswordChange(): void
    {
        $this->loginCustomerUser();

        $this->checkAccessTokenIsValid();

        $this->changeCustomerUserPassword();

        $this->checkAccessTokenIsRevoked();
    }

    private function loginCustomerUser(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/LoginMutation.graphql',
            $this->getDefaultCredentials(),
        );
        $responseData = $this->getResponseDataForGraphQlType($response, 'Login');

        $this->assertArrayHasKey('tokens', $responseData);
        $this->assertArrayHasKey('accessToken', $responseData['tokens']);
        $accessToken = $responseData['tokens']['accessToken'];

        $clientOptions = ['HTTP_X-Auth-Token' => sprintf('Bearer %s', $accessToken)];
        $this->configureCurrentClient(null, null, $clientOptions);
    }

    private function checkAccessTokenIsValid(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/CurrentCustomerUserQuery.graphql',
        );
        $responseData = $this->getResponseDataForGraphQlType($response, 'currentCustomerUser');

        $this->assertArrayHasKey('email', $responseData);
        $this->assertSame($this->getDefaultCredentials()['email'], $responseData['email']);
    }

    private function changeCustomerUserPassword(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/ChangePasswordMutation.graphql',
            [
                'email' => $this->getDefaultCredentials()['email'],
                'oldPassword' => $this->getDefaultCredentials()['password'],
                'newPassword' => 'user124',
            ],
        );
        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangePassword');

        $this->assertArrayHasKey('email', $responseData);
        $this->assertSame($this->getDefaultCredentials()['email'], $responseData['email']);
    }

    private function checkAccessTokenIsRevoked(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/query/CurrentCustomerUserQuery.graphql',
        );
        $this->assertResponseContainsArrayOfErrors($response);

        $this->assertSame('Token is not valid.', $response['errors'][0]['message']);
        $this->assertSame('invalid-token', $response['errors'][0]['extensions']['userCode']);
    }

    /**
     * @return array<string, string>
     */
    private function getDefaultCredentials(): array
    {
        return [
            'email' => 'no-reply@shopsys.com',
            'password' => 'user123',
        ];
    }
}
