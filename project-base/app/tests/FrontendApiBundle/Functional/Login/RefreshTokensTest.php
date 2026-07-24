<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Login;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class RefreshTokensTest extends GraphQlTestCase
{
    public function testRefreshTokenCanBeReusedWithinGracePeriod(): void
    {
        $initialRefreshToken = $this->loginAndGetRefreshToken();

        $firstRefreshTokensResponseData = $this->refreshTokens($initialRefreshToken);
        $secondRefreshTokensResponseData = $this->refreshTokens($initialRefreshToken);

        $this->assertNotSame($initialRefreshToken, $firstRefreshTokensResponseData['refreshToken']);
        $this->assertSame($firstRefreshTokensResponseData, $secondRefreshTokensResponseData);
    }

    private function loginAndGetRefreshToken(): string
    {
        $loginResponse = $this->getResponseContentForGql(
            __DIR__ . '/graphql/LoginMutation.graphql',
            $this->getDefaultCredentials(),
        );
        $loginResponseData = $this->getResponseDataForGraphQlType($loginResponse, 'Login');

        $this->assertArrayHasKey('tokens', $loginResponseData);
        $this->assertIsArray($loginResponseData['tokens']);
        $this->assertArrayHasKey('refreshToken', $loginResponseData['tokens']);

        return $loginResponseData['tokens']['refreshToken'];
    }

    /**
     * @return array{accessToken: string, refreshToken: string}
     */
    private function refreshTokens(string $refreshToken): array
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/RefreshTokensMutation.graphql',
            ['refreshToken' => $refreshToken],
        );

        $responseData = $this->getResponseDataForGraphQlType($response, 'RefreshTokens');

        $this->assertArrayHasKey('accessToken', $responseData);
        $this->assertArrayHasKey('refreshToken', $responseData);

        return [
            'accessToken' => $responseData['accessToken'],
            'refreshToken' => $responseData['refreshToken'],
        ];
    }

    /**
     * @return array{email: string, password: string}
     */
    private function getDefaultCredentials(): array
    {
        return [
            'email' => 'no-reply@shopsys.com',
            'password' => 'user123',
        ];
    }
}
