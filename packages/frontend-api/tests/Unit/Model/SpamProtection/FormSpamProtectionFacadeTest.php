<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\SpamProtection;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Shopsys\FrontendApiBundle\Component\HttpFoundation\ClientIpProvider;
use Shopsys\FrontendApiBundle\Model\SpamProtection\Exception\HoneyPotFieldNameNotConfiguredException;
use Shopsys\FrontendApiBundle\Model\SpamProtection\Exception\TooManyFormSubmissionsUserError;
use Shopsys\FrontendApiBundle\Model\SpamProtection\FormSpamProtectionFacade;
use Shopsys\FrontendApiBundle\Model\SpamProtection\SpamProtectedFormEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

class FormSpamProtectionFacadeTest extends TestCase
{
    private const int RATE_LIMIT = 3;
    private const int UNREACHABLE_RATE_LIMIT = 1000;
    private const string CLIENT_IP = '10.255.0.1';
    private const string SECOND_CLIENT_IP = '10.255.0.2';
    private const string SECOND_FORM_NAME = 'newsletter';
    private const string SECOND_FORM_HONEY_POT_FIELD_NAME = 'nickname';
    private const string FORM_NAME_WITHOUT_HONEY_POT_FIELD_NAME = 'unconfigured';

    /**
     * @param array<string, mixed> $input
     */
    #[DataProvider('honeyPotInputProvider')]
    public function testSubmissionIsDiscardedOnlyWhenTheHoneyPotCarriesValue(
        array $input,
        bool $expectedIsDiscarded,
    ): void {
        $facade = $this->createFacade(self::CLIENT_IP, self::UNREACHABLE_RATE_LIMIT);

        self::assertSame(
            $expectedIsDiscarded,
            $facade->shouldDiscardSubmission($input, SpamProtectedFormEnum::CONTACT_FORM),
        );
    }

    /**
     * @return iterable<string, array{array<string, mixed>, bool}>
     */
    public static function honeyPotInputProvider(): iterable
    {
        yield 'missing field is not filled' => [['name' => 'Name'], false];

        yield 'null value is not filled' => [['subject' => null], false];

        yield 'empty string is not filled' => [['subject' => ''], false];

        yield 'whitespace only is not filled' => [['subject' => "  \t\n "], false];

        yield 'non string value is not filled' => [['subject' => 42], false];

        yield 'text is filled' => [['subject' => 'Cheap pills'], true];

        yield 'text surrounded by whitespace is filled' => [['subject' => '  spam  '], true];
    }

    public function testEveryFormHasItsOwnHoneyPotFieldName(): void
    {
        $facade = $this->createFacade(self::CLIENT_IP, self::UNREACHABLE_RATE_LIMIT);

        self::assertFalse($facade->shouldDiscardSubmission(['subject' => 'Cheap pills'], self::SECOND_FORM_NAME));
        self::assertTrue(
            $facade->shouldDiscardSubmission(
                [self::SECOND_FORM_HONEY_POT_FIELD_NAME => 'Cheap pills'],
                self::SECOND_FORM_NAME,
            ),
        );
    }

    public function testSubmissionIsRefusedOnlyAfterTheRateLimitIsReached(): void
    {
        $facade = $this->createFacade(self::CLIENT_IP);

        for ($attempt = 0; $attempt < self::RATE_LIMIT; $attempt++) {
            self::assertFalse($facade->shouldDiscardSubmission([], SpamProtectedFormEnum::CONTACT_FORM));
        }

        $this->expectException(TooManyFormSubmissionsUserError::class);
        $facade->shouldDiscardSubmission([], SpamProtectedFormEnum::CONTACT_FORM);
    }

    public function testHoneyPotSubmissionsAreCountedTowardsTheRateLimit(): void
    {
        $facade = $this->createFacade(self::CLIENT_IP);
        $honeyPotInput = ['subject' => 'Cheap pills'];

        for ($attempt = 0; $attempt < self::RATE_LIMIT; $attempt++) {
            self::assertTrue($facade->shouldDiscardSubmission($honeyPotInput, SpamProtectedFormEnum::CONTACT_FORM));
        }

        $this->expectException(TooManyFormSubmissionsUserError::class);
        $facade->shouldDiscardSubmission($honeyPotInput, SpamProtectedFormEnum::CONTACT_FORM);
    }

    public function testRateLimitIsCountedPerClientIp(): void
    {
        $rateLimiterFactory = $this->createRateLimiterFactory(self::RATE_LIMIT);
        $firstClientFacade = $this->createFacade(self::CLIENT_IP, rateLimiterFactory: $rateLimiterFactory);
        $secondClientFacade = $this->createFacade(self::SECOND_CLIENT_IP, rateLimiterFactory: $rateLimiterFactory);

        $this->exhaustRateLimit($firstClientFacade, SpamProtectedFormEnum::CONTACT_FORM);

        self::assertFalse($secondClientFacade->shouldDiscardSubmission([], SpamProtectedFormEnum::CONTACT_FORM));
    }

    public function testRateLimitIsCountedPerFormName(): void
    {
        $facade = $this->createFacade(self::CLIENT_IP);

        $this->exhaustRateLimit($facade, SpamProtectedFormEnum::CONTACT_FORM);

        self::assertFalse($facade->shouldDiscardSubmission([], self::SECOND_FORM_NAME));
    }

    public function testFormWithoutConfiguredHoneyPotFieldNameIsRefused(): void
    {
        $facade = $this->createFacade(self::CLIENT_IP);

        $this->expectException(HoneyPotFieldNameNotConfiguredException::class);
        $facade->shouldDiscardSubmission([], self::FORM_NAME_WITHOUT_HONEY_POT_FIELD_NAME);
    }

    public function testAllSubmissionsShareOneKeyWhenTheClientIpIsNotKnown(): void
    {
        $facade = new FormSpamProtectionFacade(
            new ClientIpProvider(new RequestStack()),
            new NullLogger(),
            $this->createRateLimiterFactory(self::RATE_LIMIT),
            $this->createSpamProtectedFormEnum(),
        );

        $this->exhaustRateLimit($facade, SpamProtectedFormEnum::CONTACT_FORM);

        $this->expectException(TooManyFormSubmissionsUserError::class);
        $facade->shouldDiscardSubmission([], SpamProtectedFormEnum::CONTACT_FORM);
    }

    private function exhaustRateLimit(FormSpamProtectionFacade $facade, string $formName): void
    {
        for ($attempt = 0; $attempt < self::RATE_LIMIT; $attempt++) {
            $facade->shouldDiscardSubmission([], $formName);
        }
    }

    private function createFacade(
        string $clientIp,
        int $rateLimit = self::RATE_LIMIT,
        ?RateLimiterFactoryInterface $rateLimiterFactory = null,
    ): FormSpamProtectionFacade {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create('/graphql', 'POST', server: ['REMOTE_ADDR' => $clientIp]));

        return new FormSpamProtectionFacade(
            new ClientIpProvider($requestStack),
            new NullLogger(),
            $rateLimiterFactory ?? $this->createRateLimiterFactory($rateLimit),
            $this->createSpamProtectedFormEnum(),
        );
    }

    private function createRateLimiterFactory(int $rateLimit): RateLimiterFactory
    {
        return new RateLimiterFactory([
            'id' => 'form-spam-protection-test',
            'policy' => 'fixed_window',
            'limit' => $rateLimit,
            'interval' => '5 minutes',
        ], new InMemoryStorage());
    }

    private function createSpamProtectedFormEnum(): SpamProtectedFormEnum
    {
        return new class() extends SpamProtectedFormEnum {
            public const string NEWSLETTER = 'newsletter';

            /**
             * @return array<string, string>
             */
            #[Override]
            public function getHoneyPotFieldNameIndexedByFormName(): array
            {
                return array_merge(
                    parent::getHoneyPotFieldNameIndexedByFormName(),
                    [static::NEWSLETTER => 'nickname'],
                );
            }
        };
    }
}
