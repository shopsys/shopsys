<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Login;

use Tests\FrontendApiBundle\Test\CommonGraphQlWithLoginTestCase;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class LoginRateLimitTest extends GraphQlTestCase
{
    /**
     * The firewall allows 5 attempts per username and 5 × 5 attempts per IP within a minute
     */
    private const int MAX_ATTEMPTS_PER_USERNAME = 5;
    private const int MAX_ATTEMPTS_PER_IP = 25;

    public function testSuccessfulLoginsDoNotExhaustRateLimit(): void
    {
        $loginsCount = self::MAX_ATTEMPTS_PER_IP + 1;

        for ($i = 0; $i < $loginsCount; $i++) {
            $response = $this->getResponseContentForGql(__DIR__ . '/graphql/LoginMutation.graphql', [
                'email' => CommonGraphQlWithLoginTestCase::DEFAULT_USER_EMAIL,
                'password' => CommonGraphQlWithLoginTestCase::DEFAULT_USER_PASSWORD,
            ]);

            $this->assertIsString($this->getResponseDataForGraphQlType($response, 'Login')['tokens']['accessToken']);
        }
    }

    public function testFailedLoginsAreRateLimitedPerUsername(): void
    {
        // unique e-mail keeps the username+IP limit of this test isolated from the other tests and previous runs
        $variables = [
            'email' => sprintf('rate-limit-%s@shopsys.com', uniqid('', true)),
            'password' => 'wrong-password',
        ];

        for ($i = 0; $i < self::MAX_ATTEMPTS_PER_USERNAME; $i++) {
            $response = $this->getResponseContentForGql(__DIR__ . '/graphql/LoginMutation.graphql', $variables);

            $this->assertUserError($response, 'invalid-credentials');
        }

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/LoginMutation.graphql', $variables);

        $this->assertUserError($response, 'too-many-login-attempts');
    }
}
