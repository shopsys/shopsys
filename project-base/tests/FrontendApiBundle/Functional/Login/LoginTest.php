<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Login;

use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class LoginTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrontendApiBundle\Model\Token\TokenFacade
     * @inject
     */
    protected $tokenFacade;

    public function testLoginMutation(): void
    {
        $responseData = $this->getResponseContentForQuery($this->getLoginQuery())['data']['Login'];
        $this->assertArrayHasKey('accessToken', $responseData);
        $this->assertIsString($responseData['accessToken']);
        $this->assertArrayHasKey('refreshToken', $responseData);
        $this->assertIsString($responseData['refreshToken']);

        try {
            $this->tokenFacade->getTokenByString($responseData['accessToken']);
        } catch (\Throwable $throwable) {
            $this->fail('Token is not valid');
        }

        $authorizationResponseData = $this->getResponseContentForQuery($this->getLoginQuery(), [], ['HTTP_Authorization' => sprintf('Bearer %s', $responseData['accessToken'])])['data']['Login'];
        $this->assertArrayHasKey('accessToken', $authorizationResponseData);
        $this->assertIsString($authorizationResponseData['accessToken']);
        $this->assertArrayHasKey('refreshToken', $authorizationResponseData);
        $this->assertIsString($authorizationResponseData['refreshToken']);
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
                'message' => 'Token is not valid.',
                'extensions' => [
                    'category' => 'token',
                ],
            ],
        ];

        $response = $this->getResponseContentForQuery($this->getLoginQuery(), [], ['HTTP_Authorization' => 'Bearer 123']);
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
