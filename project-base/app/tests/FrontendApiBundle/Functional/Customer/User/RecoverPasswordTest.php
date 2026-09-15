<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\FrontendApi\Model\Component\Constraints\ExistingEmail;
use App\FrontendApi\Model\Component\Constraints\ResetPasswordHash;
use App\Model\Customer\User\CustomerUser;
use Override;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RecoverPasswordTest extends GraphQlTestCase
{
    use PasswordRecoveryRateLimitTrait;

    private const string NOT_EXISTING_EMAIL = 'no-reply-not-existing@shopsys.com';

    private const string INVALID_RESET_PASSWORD_HASH = 'Lorem ipsum dolor sit amet, consectetur tincidunt.';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearRateLimits();
    }

    public function testRequestPasswordRecovery(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH, CustomerUser::class);

        $response = $this->getRecoverPasswordResponse(
            $customerUser->getEmail(),
            (string)$customerUser->getResetPasswordHash(),
        );

        $recoverPasswordData = $this->getResponseDataForGraphQlType($response, 'RecoverPassword');

        $this->assertArrayHasKey('tokens', $recoverPasswordData);
        $this->assertIsString($recoverPasswordData['tokens']['accessToken']);

        $this->assertArrayHasKey('tokens', $recoverPasswordData);
        $this->assertIsString($recoverPasswordData['tokens']['refreshToken']);
    }

    public function testRequestPasswordRecoveryWithInvalidHash(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH, CustomerUser::class);

        $response = $this->getRecoverPasswordResponse(
            $customerUser->getEmail(),
            self::INVALID_RESET_PASSWORD_HASH,
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertCount(1, $validationErrors);
        $this->assertSame(ResetPasswordHash::INVALID_HASH_ERROR, $validationErrors['input.hash'][0]['code']);
    }

    public function testRequestPasswordRecoveryWithInvalidEmail(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH, CustomerUser::class);

        $this->assertValidationErrorForNotExistingEmail(
            $this->getRecoverPasswordResponse(
                self::NOT_EXISTING_EMAIL,
                (string)$customerUser->getResetPasswordHash(),
            ),
        );
    }

    public function testRecoverPasswordAttemptsAreCountedTowardsTheIpRateLimit(): void
    {
        $this->getRecoverPasswordResponse(self::NOT_EXISTING_EMAIL, self::INVALID_RESET_PASSWORD_HASH);
        $remainingTokens = $this->peekRateLimit(self::IP_RATE_LIMITER_SERVICE_ID, $this->getClientIp())
            ->getRemainingTokens();

        $this->getRecoverPasswordResponse(self::NOT_EXISTING_EMAIL, self::INVALID_RESET_PASSWORD_HASH);

        $this->assertSame(
            $remainingTokens - 1,
            $this->peekRateLimit(self::IP_RATE_LIMITER_SERVICE_ID, $this->getClientIp())->getRemainingTokens(),
        );
    }

    public function testRecoverPasswordStopsRevealingExistingAccountsOnceTheIpRateLimitIsExceeded(): void
    {
        $this->assertValidationErrorForNotExistingEmail(
            $this->getRecoverPasswordResponse(self::NOT_EXISTING_EMAIL, self::INVALID_RESET_PASSWORD_HASH),
        );

        $this->exhaustIpRateLimit();

        $this->assertUserError(
            $this->getRecoverPasswordResponse(self::NOT_EXISTING_EMAIL, self::INVALID_RESET_PASSWORD_HASH),
            self::TOO_MANY_ATTEMPTS_USER_CODE,
        );
    }

    /**
     * @param array<string, mixed> $response
     */
    private function assertValidationErrorForNotExistingEmail(array $response): void
    {
        $this->assertResponseContainsArrayOfErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertCount(1, $validationErrors);
        $this->assertSame(
            ExistingEmail::USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR,
            $validationErrors['input.email'][0]['code'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getRecoverPasswordResponse(string $email, string $hash): array
    {
        return $this->getResponseContentForQuery('
            mutation {
                RecoverPassword(input: {
                    email: "' . $email . '"
                    hash: "' . $hash . '"
                    newPassword: "password123"
                }) {
                    tokens {
                        accessToken
                        refreshToken
                    }
                }
            }');
    }
}
