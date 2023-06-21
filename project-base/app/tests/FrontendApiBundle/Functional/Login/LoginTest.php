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
     * @inject
     */
    protected TokenFacade $tokenFacade;

    public function testLoginMutation(): void
    {
        $graphQlType = 'Login';
        $response = $this->getResponseContentForQuery(self::getLoginQuery());

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey('tokens', $responseData);
        $this->assertIsString($responseData['tokens']['accessToken']);

        $this->assertArrayHasKey('tokens', $responseData);
        $this->assertIsString($responseData['tokens']['refreshToken']);

        try {
            $this->tokenFacade->getTokenByString($responseData['tokens']['accessToken']);
        } catch (Throwable $throwable) {
            $this->fail('Token is not valid');
        }

        $clientOptions = ['HTTP_X-Auth-Token' => sprintf('Bearer %s', $responseData['tokens']['accessToken'])];
        $this->configureCurrentClient(null, null, $clientOptions);
        $authorizationResponse = $this->getResponseContentForQuery(self::getLoginQuery());

        $this->assertResponseContainsArrayOfDataForGraphQlType($authorizationResponse, $graphQlType);
        $authorizationResponseData = $this->getResponseDataForGraphQlType($authorizationResponse, $graphQlType);

        $this->assertArrayHasKey('tokens', $authorizationResponseData);
        $this->assertIsString($authorizationResponseData['tokens']['accessToken']);

        $this->assertArrayHasKey('tokens', $authorizationResponseData);
        $this->assertIsString($authorizationResponseData['tokens']['refreshToken']);
    }

    public function testInvalidTokenException()
    {
        $this->expectException(InvalidTokenUserMessageException::class);
        $this->tokenFacade->getTokenByString('abcd');
    }

    public function testInvalidTokenInHeader()
    {
        $expectedError = [
            'errors' => [
                [
                    'message' => 'Token is not valid.',
                    'extensions' => [
                        'category' => 'token',
                        'userCode' => 'invalid-token',
                    ],
                ],
            ],
        ];

        $this->configureCurrentClient(null, null, ['HTTP_X-Auth-Token' => 'Bearer 123']);

        $response = $this->getResponseContentForQuery(self::getLoginQuery());

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
                    tokens {
                        accessToken
                        refreshToken
                    }
                }
            }
        ';
    }
}
