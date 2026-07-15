<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Login;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class RefreshTokensTest extends GraphQlTestCase
{
    public function testRefreshTokensMutationRotatesTokenAndKeepsExplicitTokenContract(): void
    {
        $loginResponse = $this->getResponseContentForGql(
            __DIR__ . '/graphql/LoginMutation.graphql',
            [
                'email' => 'no-reply@shopsys.com',
                'password' => 'user123',
            ],
        );
        $loginData = $this->getResponseDataForGraphQlType($loginResponse, 'Login');
        $originalRefreshToken = $loginData['tokens']['refreshToken'];

        $refreshResponse = $this->getResponseContentForGql(
            __DIR__ . '/graphql/RefreshTokensMutation.graphql',
            ['refreshToken' => $originalRefreshToken],
        );
        $refreshData = $this->getResponseDataForGraphQlType($refreshResponse, 'RefreshTokens');

        $this->assertIsString($refreshData['accessToken']);
        $this->assertIsString($refreshData['refreshToken']);
        $this->assertNotSame($originalRefreshToken, $refreshData['refreshToken']);

        $reusedRefreshTokenResponse = $this->getResponseContentForGql(
            __DIR__ . '/graphql/RefreshTokensMutation.graphql',
            ['refreshToken' => $originalRefreshToken],
        );

        $this->assertUserError($reusedRefreshTokenResponse, 'invalid-token');
    }
}
