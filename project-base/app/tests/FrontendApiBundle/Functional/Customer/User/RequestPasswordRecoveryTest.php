<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Customer\User;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\FrontendApi\Model\Component\Constraints\ExistingEmail;
use App\Model\Customer\User\CustomerUser;
use Override;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RequestPasswordRecoveryTest extends GraphQlTestCase
{
    private const string EMAIL_RATE_LIMITER_SERVICE_ID = 'limiter.frontend_api_password_recovery_email';

    private const string IP_RATE_LIMITER_SERVICE_ID = 'limiter.frontend_api_password_recovery_ip';

    private const string RATE_LIMITER_CACHE_POOL_SERVICE_ID = 'frontend_api_password_recovery_rate_limiter_cache';

    private const string NOT_EXISTING_EMAIL = 'does-not-exist@shopsys.com';

    private const string TOO_MANY_ATTEMPTS_USER_CODE = 'too-many-password-recovery-attempts';

    private const string ANY_RATE_LIMITER_KEY = 'any-key';

    private const string DIFFERENT_CLIENT_IP = '10.255.0.1';

    private string $existingEmail;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Psr\Cache\CacheItemPoolInterface $rateLimiterCachePool */
        $rateLimiterCachePool = self::getContainer()->get(self::RATE_LIMITER_CACHE_POOL_SERVICE_ID);
        $rateLimiterCachePool->clear();

        $this->existingEmail = $this->getReference(
            CustomerUserDataFixture::USER_WITH_RESET_PASSWORD_HASH,
            CustomerUser::class,
        )->getEmail();
    }

    public function testRequestPasswordRecoveryForExistingUser(): void
    {
        $this->assertPasswordRecoveryRequested($this->getPasswordRecoveryResponse($this->existingEmail));
    }

    public function testRequestPasswordRecoveryForNotExistingUser(): void
    {
        $this->assertValidationErrorForNotExistingEmail($this->getPasswordRecoveryResponse(self::NOT_EXISTING_EMAIL));
    }

    public function testSuccessfulRequestsAreRateLimitedPerNormalizedEmail(): void
    {
        $emailRateLimit = $this->peekRateLimit(self::EMAIL_RATE_LIMITER_SERVICE_ID, self::ANY_RATE_LIMITER_KEY)
            ->getLimit();

        for ($attempt = 0; $attempt < $emailRateLimit; $attempt++) {
            $email = $attempt % 2 === 0 ? $this->existingEmail : mb_strtoupper($this->existingEmail);

            $this->assertPasswordRecoveryRequested($this->getPasswordRecoveryResponse($email));
        }

        $this->assertUserError(
            $this->getPasswordRecoveryResponse($this->existingEmail),
            self::TOO_MANY_ATTEMPTS_USER_CODE,
        );
    }

    public function testAttemptsRejectedByValidationAreCountedTowardsTheIpRateLimit(): void
    {
        $this->assertValidationErrorForNotExistingEmail($this->getPasswordRecoveryResponse(self::NOT_EXISTING_EMAIL));
        $remainingTokens = $this->peekRateLimit(self::IP_RATE_LIMITER_SERVICE_ID, $this->getClientIp())
            ->getRemainingTokens();

        $this->assertValidationErrorForNotExistingEmail($this->getPasswordRecoveryResponse(self::NOT_EXISTING_EMAIL));

        $this->assertSame(
            $remainingTokens - 1,
            $this->peekRateLimit(self::IP_RATE_LIMITER_SERVICE_ID, $this->getClientIp())->getRemainingTokens(),
        );
    }

    public function testAttemptsRejectedByValidationAreCountedTowardsTheEmailRateLimit(): void
    {
        $this->exhaustEmailRateLimit(self::NOT_EXISTING_EMAIL);

        $this->assertUserError(
            $this->getPasswordRecoveryResponse(self::NOT_EXISTING_EMAIL),
            self::TOO_MANY_ATTEMPTS_USER_CODE,
        );
    }

    public function testEmailRateLimitIsScopedToTheSubmittedEmail(): void
    {
        $this->exhaustEmailRateLimit($this->existingEmail);

        $this->assertUserError(
            $this->getPasswordRecoveryResponse($this->existingEmail),
            self::TOO_MANY_ATTEMPTS_USER_CODE,
        );
        $this->assertPasswordRecoveryRequested(
            $this->getPasswordRecoveryResponse(
                CustomerUserDataFixture::USER_WITH_DELIVERY_ADDRESS_PERSISTENT_REFERENCE_EMAIL,
            ),
        );
    }

    public function testEmailRateLimitAppliesEvenWhenTheClientIpChanges(): void
    {
        $this->exhaustEmailRateLimit($this->existingEmail);

        $this->configureCurrentClient(null, null, ['REMOTE_ADDR' => self::DIFFERENT_CLIENT_IP]);

        $this->assertUserError(
            $this->getPasswordRecoveryResponse($this->existingEmail),
            self::TOO_MANY_ATTEMPTS_USER_CODE,
        );
    }

    public function testRequestPasswordRecoveryIsRateLimitedPerIpRegardlessOfEmail(): void
    {
        $this->assertValidationErrorForNotExistingEmail($this->getPasswordRecoveryResponse(self::NOT_EXISTING_EMAIL));

        $this->exhaustIpRateLimit();

        $responseForExistingEmail = $this->getPasswordRecoveryResponse($this->existingEmail);
        $responseForNotExistingEmail = $this->getPasswordRecoveryResponse(self::NOT_EXISTING_EMAIL);

        $this->assertUserError($responseForExistingEmail, self::TOO_MANY_ATTEMPTS_USER_CODE);
        $this->assertSame($responseForExistingEmail, $responseForNotExistingEmail);
    }

    /**
     * @return array<string, mixed>
     */
    private function getPasswordRecoveryResponse(string $email): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/RequestPasswordRecoveryMutation.graphql', [
            'email' => $email,
        ]);
    }

    /**
     * @param array<string, mixed> $response
     */
    private function assertPasswordRecoveryRequested(array $response): void
    {
        $this->assertSame([
            'data' => [
                'RequestPasswordRecovery' => 'success',
            ],
        ], $response);
    }

    /**
     * @param array<string, mixed> $response
     */
    private function assertValidationErrorForNotExistingEmail(array $response): void
    {
        $this->assertResponseContainsArrayOfErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertCount(1, $validationErrors);
        $this->assertSame(ExistingEmail::USER_WITH_EMAIL_DOES_NOT_EXIST_ERROR, $validationErrors['email'][0]['code']);
    }

    private function exhaustEmailRateLimit(string $email): void
    {
        $emailRateLimit = $this->peekRateLimit(self::EMAIL_RATE_LIMITER_SERVICE_ID, self::ANY_RATE_LIMITER_KEY)
            ->getLimit();

        for ($attempt = 0; $attempt < $emailRateLimit; $attempt++) {
            $this->getPasswordRecoveryResponse($email);
        }
    }

    private function exhaustIpRateLimit(): void
    {
        $rateLimiter = $this->getRateLimiterFactory(self::IP_RATE_LIMITER_SERVICE_ID)->create($this->getClientIp());

        do {
            $rateLimit = $rateLimiter->consume();
        } while ($rateLimit->isAccepted());
    }

    private function peekRateLimit(string $rateLimiterServiceId, string $key): RateLimit
    {
        return $this->getRateLimiterFactory($rateLimiterServiceId)->create($key)->consume(0);
    }

    private function getClientIp(): string
    {
        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = self::getCurrentClient()->getRequest();

        return (string)$request->getClientIp();
    }

    private function getRateLimiterFactory(string $rateLimiterServiceId): RateLimiterFactoryInterface
    {
        /** @var \Symfony\Component\RateLimiter\RateLimiterFactoryInterface $rateLimiterFactory */
        $rateLimiterFactory = self::getContainer()->get($rateLimiterServiceId);

        return $rateLimiterFactory;
    }
}
