<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Model\Administrator\McpToken;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenData;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenHasher;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenLookup;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenRepository;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

class AdministratorMcpTokenLookupTest extends TestCase
{
    private const string MOCKED_NOW = '2026-03-18 13:30:00';
    private const string VALID_PUBLIC_TOKEN_ID = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    private const string VALID_SECRET = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';
    private const string OTHER_VALID_SECRET = 'cccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccc';

    public function testFindValidTokenByTokenStringReturnsTokenForMatchingPublicIdAndSecret(): void
    {
        $administratorMcpTokenHasher = $this->createAdministratorMcpTokenHasher();
        $administratorMcpToken = $this->createAdministratorMcpToken(
            self::VALID_PUBLIC_TOKEN_ID,
            $administratorMcpTokenHasher->hash(self::VALID_SECRET),
        );
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([self::VALID_PUBLIC_TOKEN_ID => $administratorMcpToken]),
            $administratorMcpTokenHasher,
            $this->createClock(),
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString($this->createTokenString(
            self::VALID_PUBLIC_TOKEN_ID,
            self::VALID_SECRET,
        ));

        $this->assertSame($administratorMcpToken, $foundAdministratorMcpToken);
    }

    public function testFindValidTokenByTokenStringReturnsNullForInvalidSecret(): void
    {
        $administratorMcpTokenHasher = $this->createAdministratorMcpTokenHasher();
        $administratorMcpToken = $this->createAdministratorMcpToken(
            self::VALID_PUBLIC_TOKEN_ID,
            $administratorMcpTokenHasher->hash(self::VALID_SECRET),
        );
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([self::VALID_PUBLIC_TOKEN_ID => $administratorMcpToken]),
            $administratorMcpTokenHasher,
            $this->createClock(),
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString($this->createTokenString(
            self::VALID_PUBLIC_TOKEN_ID,
            self::OTHER_VALID_SECRET,
        ));

        $this->assertNull($foundAdministratorMcpToken);
    }

    public function testFindValidTokenByTokenStringReturnsNullForMalformedToken(): void
    {
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([]),
            $this->createAdministratorMcpTokenHasher(),
            $this->createClock(),
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString('malformed-token');

        $this->assertNull($foundAdministratorMcpToken);
    }

    public function testFindValidTokenByTokenStringReturnsNullForTokenWithInvalidCharacters(): void
    {
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([]),
            $this->createAdministratorMcpTokenHasher(),
            $this->createClock(),
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString('zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz.' . self::VALID_SECRET);

        $this->assertNull($foundAdministratorMcpToken);
    }

    public function testFindValidTokenByTokenStringReturnsNullWhenPublicTokenIdDoesNotExist(): void
    {
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([]),
            $this->createAdministratorMcpTokenHasher(),
            $this->createClock(),
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString($this->createTokenString(
            self::VALID_PUBLIC_TOKEN_ID,
            self::VALID_SECRET,
        ));

        $this->assertNull($foundAdministratorMcpToken);
    }

    public function testFindValidTokenByTokenStringReturnsNullForExpiredToken(): void
    {
        $administratorMcpTokenHasher = $this->createAdministratorMcpTokenHasher();
        $administratorMcpToken = $this->createAdministratorMcpToken(
            self::VALID_PUBLIC_TOKEN_ID,
            $administratorMcpTokenHasher->hash(self::VALID_SECRET),
            '-15 minutes',
        );
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([self::VALID_PUBLIC_TOKEN_ID => $administratorMcpToken]),
            $administratorMcpTokenHasher,
            $this->createClock(),
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString($this->createTokenString(
            self::VALID_PUBLIC_TOKEN_ID,
            self::VALID_SECRET,
        ));

        $this->assertNull($foundAdministratorMcpToken);
    }

    private function createAdministratorMcpTokenHasher(): AdministratorMcpTokenHasher
    {
        $passwordHasherFactory = new PasswordHasherFactory([
            Administrator::class => [
                'algorithm' => 'bcrypt',
                'cost' => 4,
            ],
        ]);

        return new AdministratorMcpTokenHasher($passwordHasherFactory);
    }

    private function createAdministratorMcpToken(
        string $publicTokenId,
        string $secretHash,
        string $expiresAtModification = '+30 minutes',
    ): AdministratorMcpToken {
        $administratorMcpTokenData = new AdministratorMcpTokenData();
        $administratorMcpTokenData->administrator = $this->createStub(Administrator::class);
        $administratorMcpTokenData->publicTokenId = $publicTokenId;
        $administratorMcpTokenData->secretHash = $secretHash;
        $administratorMcpTokenData->type = AdministratorMcpToken::TYPE_MANUAL;
        $administratorMcpTokenData->clientId = null;
        $administratorMcpTokenData->label = AdministratorMcpToken::DEFAULT_MANUAL_TOKEN_LABEL;
        $administratorMcpTokenData->createdAt = new DateTimeImmutable(self::MOCKED_NOW);
        $administratorMcpTokenData->expiresAt = $administratorMcpTokenData->createdAt->modify($expiresAtModification);

        return new AdministratorMcpToken($administratorMcpTokenData);
    }

    private function createClock(): ClockInterface
    {
        $clockStub = $this->createStub(ClockInterface::class);
        $clockStub->method('now')->willReturn(new DateTimeImmutable(self::MOCKED_NOW));

        return $clockStub;
    }

    private function createTokenString(string $publicTokenId, string $secret): string
    {
        return $publicTokenId . '.' . $secret;
    }

    /**
     * @param array<string, \Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken> $administratorMcpTokensByPublicTokenId
     */
    private function createAdministratorMcpTokenRepository(
        array $administratorMcpTokensByPublicTokenId,
    ): AdministratorMcpTokenRepository {
        return new class($administratorMcpTokensByPublicTokenId, $this->createStub(EntityManagerInterface::class)) extends AdministratorMcpTokenRepository {
            /**
             * @param array<string, \Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken> $administratorMcpTokensByPublicTokenId
             */
            public function __construct(
                protected readonly array $administratorMcpTokensByPublicTokenId,
                EntityManagerInterface $entityManager,
            ) {
                parent::__construct($entityManager);
            }

            public function findActiveByPublicTokenId(
                string $publicTokenId,
                DateTimeImmutable $dateTime,
            ): ?AdministratorMcpToken {
                return $this->administratorMcpTokensByPublicTokenId[$publicTokenId] ?? null;
            }
        };
    }
}
