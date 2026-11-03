<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use Override;
use Symfony\Component\Validator\Constraints\Email;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class IsCustomerUserRegisteredTest extends GraphQlTestCase
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

    public function testIsUserRegisteredWithRegisteredEmail(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/IsCustomerUserRegisteredQuery.graphql', [
            'email' => 'no-reply@shopsys.com',
        ]);

        $this->assertTrue($response['data']['isCustomerUserRegistered']);
    }

    public function testIsUserRegisteredWithNotRegisteredEmail(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/IsCustomerUserRegisteredQuery.graphql', [
            'email' => 'not-registered@shopsys.com',
        ]);

        $this->assertFalse($response['data']['isCustomerUserRegistered']);
    }

    public function testIsUserRegisteredWithInvalidEmail(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/IsCustomerUserRegisteredQuery.graphql', [
            'email' => 'invalidEmail',
        ]);

        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);

        $this->assertSame(Email::INVALID_FORMAT_ERROR, $validationErrors['email'][0]['code']);
    }

    public function testIsRateLimitedPerClientIp(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/IsCustomerUserRegisteredQuery.graphql', [
            'email' => 'no-reply@shopsys.com',
        ]);
        $this->assertTrue($this->getResponseDataForGraphQlType($response, 'isCustomerUserRegistered'));
        $this->exhaustCustomerRegistrationRateLimit();

        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/IsCustomerUserRegisteredQuery.graphql', [
            'email' => 'no-reply@shopsys.com',
        ]);

        $this->assertUserError($response, self::TOO_MANY_CUSTOMER_REGISTRATION_ATTEMPTS_USER_CODE);

        $this->configureCurrentClient(null, null, ['REMOTE_ADDR' => self::DIFFERENT_CLIENT_IP]);
        $response = $this->getResponseContentForGql(__DIR__ . '/../../_graphql/query/IsCustomerUserRegisteredQuery.graphql', [
            'email' => 'no-reply@shopsys.com',
        ]);

        $this->assertTrue($this->getResponseDataForGraphQlType($response, 'isCustomerUserRegistered'));
    }
}
