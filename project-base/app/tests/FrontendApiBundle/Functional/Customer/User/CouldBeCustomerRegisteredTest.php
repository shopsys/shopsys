<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Override;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class CouldBeCustomerRegisteredTest extends GraphQlTestCase
{
    use CustomerRegistrationRateLimitTrait;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearCustomerRegistrationRateLimits();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->clearCustomerRegistrationRateLimits();

        parent::tearDown();
    }

    public function testRegisteredEmailCannotBeRegisteredAgain(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CouldBeCustomerRegisteredQuery.graphql', [
            'email' => 'no-reply@shopsys.com',
        ]);

        $this->assertFalse($this->getResponseDataForGraphQlType($response, 'couldBeCustomerRegisteredQuery'));
    }

    public function testFreeEmailCanBeRegistered(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CouldBeCustomerRegisteredQuery.graphql', [
            'email' => 'not-registered@shopsys.com',
        ]);

        $this->assertTrue($this->getResponseDataForGraphQlType($response, 'couldBeCustomerRegisteredQuery'));
    }

    public function testIsRateLimitedPerClientIp(): void
    {
        $this->getResponseContentForGql(__DIR__ . '/graphql/CouldBeCustomerRegisteredQuery.graphql', [
            'email' => 'not-registered@shopsys.com',
        ]);
        $this->exhaustCustomerRegistrationRateLimit();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CouldBeCustomerRegisteredQuery.graphql', [
            'email' => 'not-registered@shopsys.com',
        ]);

        $this->assertUserError($response, self::TOO_MANY_CUSTOMER_REGISTRATION_ATTEMPTS_USER_CODE);

        $this->configureCurrentClient(null, null, ['REMOTE_ADDR' => self::DIFFERENT_CLIENT_IP]);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CouldBeCustomerRegisteredQuery.graphql', [
            'email' => 'not-registered@shopsys.com',
        ]);

        $this->assertTrue($this->getResponseDataForGraphQlType($response, 'couldBeCustomerRegisteredQuery'));
    }

    public function testOneQueryConsumesOneRateLimitToken(): void
    {
        $this->getResponseContentForGql(__DIR__ . '/graphql/CouldBeCustomerRegisteredQuery.graphql', [
            'email' => 'not-registered@shopsys.com',
        ]);
        $availableTokensAfterFirstQuery = $this->getRemainingCustomerRegistrationRateLimitTokens();

        $this->getResponseContentForGql(__DIR__ . '/graphql/CouldBeCustomerRegisteredQuery.graphql', [
            'email' => 'not-registered@shopsys.com',
        ]);

        $this->assertSame($availableTokensAfterFirstQuery - 1, $this->getRemainingCustomerRegistrationRateLimitTokens());
    }
}
