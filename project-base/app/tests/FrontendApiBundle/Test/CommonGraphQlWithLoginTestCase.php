<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

abstract class CommonGraphQlWithLoginTestCase extends GraphQlTestCase
{
    public const string DEFAULT_USER_EMAIL = 'no-reply@shopsys.com';
    public const string DEFAULT_USER_PASSWORD = 'user123';

    private string $currentAccessToken = '';

    /**
     * @param string|null $customerUserEmail
     * @param string|null $customerUserPassword
     * @return string
     */
    private static function getLoginQuery(
        ?string $customerUserEmail = null,
        ?string $customerUserPassword = null,
    ): string {
        $customerUserEmail = $customerUserEmail ?? self::DEFAULT_USER_EMAIL;
        $customerUserPassword = $customerUserPassword ?? self::DEFAULT_USER_PASSWORD;

        return '
            mutation {
                Login(input: {
                    email: "' . $customerUserEmail . '"
                    password: "' . $customerUserPassword . '"
                }) {
                    tokens {
                        accessToken
                        refreshToken    
                    }
                }
            }
        ';
    }

    protected function login(): void
    {
        if ($this->currentAccessToken === '') {
            $responseData = $this->getResponseContentForQuery(
                self::getLoginQuery(
                    static::DEFAULT_USER_EMAIL,
                    static::DEFAULT_USER_PASSWORD,
                ),
            );
            $this->currentAccessToken = $responseData['data']['Login']['tokens']['accessToken'];
        }

        $this->configureCurrentClient(
            null,
            null,
            [
                'CONTENT_TYPE' => 'application/graphql',
                'HTTP_X-Auth-Token' => sprintf('Bearer %s', $this->currentAccessToken),
            ],
        );
    }

    protected function logout(): void
    {
        $this->currentAccessToken = '';

        $this->configureCurrentClient(
            null,
            null,
            [
                'CONTENT_TYPE' => 'application/graphql',
            ],
        );
    }
}
