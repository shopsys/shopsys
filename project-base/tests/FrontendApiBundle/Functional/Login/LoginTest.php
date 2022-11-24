<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Login;

use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;
use Throwable;

class LoginTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrontendApiBundle\Model\Token\TokenFacade
     * @inject
     */
    protected TokenFacade $tokenFacade;

    public function testLoginMutation(): void
    {
        $graphQlType = 'Login';
        $response = $this->getResponseContentForQuery($this->getLoginQuery());

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey('accessToken', $responseData);
        $this->assertIsString($responseData['accessToken']);

        $this->assertArrayHasKey('refreshToken', $responseData);
        $this->assertIsString($responseData['refreshToken']);

        try {
            $this->tokenFacade->getTokenByString($responseData['accessToken']);
        } catch (Throwable $throwable) {
            $this->fail('Token is not valid');
        }

        $authorizationResponse = $this->getResponseContentForQuery(
            $this->getLoginQuery(),
            [],
            ['HTTP_Authorization' => sprintf('Bearer %s', $responseData['accessToken'])]
        );

        $this->assertResponseContainsArrayOfDataForGraphQlType($authorizationResponse, $graphQlType);
        $authorizationResponseData = $this->getResponseDataForGraphQlType($authorizationResponse, $graphQlType);

        $this->assertArrayHasKey('accessToken', $authorizationResponseData);
        $this->assertIsString($authorizationResponseData['accessToken']);

        $this->assertArrayHasKey('refreshToken', $authorizationResponseData);
        $this->assertIsString($authorizationResponseData['refreshToken']);
    }

    public function testInvalidTokenException(): void
    {
        $this->expectException(InvalidTokenUserMessageException::class);
        $this->tokenFacade->getTokenByString('abcd');
    }

    public function testInvalidTokenInHeader(): void
    {
        $expectedError = [
            'errors' => [
                'message' => 'Token is not valid.',
                'extensions' => [
                    'category' => 'token',
                ],
            ],
        ];

        $response = $this->getResponseContentForQuery(
            $this->getLoginQuery(),
            [],
            ['HTTP_Authorization' => 'Bearer 123']
        );
        $this->assertSame($expectedError, $response);
    }

    /**
     * @return string
     */
    private static function getLoginQuery(): string
    {
        return '
            mutation {
                Login(input: {
                    email: "no-reply@shopsys.com"
                    password: "user123"
                }) {
                    accessToken
                    refreshToken
                }
            }
        ';
    }
}
