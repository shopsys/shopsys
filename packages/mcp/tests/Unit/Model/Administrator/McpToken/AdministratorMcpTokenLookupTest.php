<?php

declare(strict_types=1);

namespace Tests\McpBundle\Unit\Model\Administrator\McpToken;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenData;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenHasher;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenLookup;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenRepository;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;

class AdministratorMcpTokenLookupTest extends TestCase
{
    public function testFindValidTokenByTokenStringReturnsTokenForMatchingPublicIdAndSecret(): void
    {
        $publicTokenId = 'public-token-id';
        $secret = 'secret-token';
        $administratorMcpTokenHasher = $this->createAdministratorMcpTokenHasher();
        $administratorMcpToken = $this->createAdministratorMcpToken(
            $publicTokenId,
            $administratorMcpTokenHasher->hash($secret),
        );
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([$publicTokenId => $administratorMcpToken]),
            $administratorMcpTokenHasher,
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString($publicTokenId . '.' . $secret);

        $this->assertSame($administratorMcpToken, $foundAdministratorMcpToken);
    }

    public function testFindValidTokenByTokenStringReturnsNullForInvalidSecret(): void
    {
        $publicTokenId = 'public-token-id';
        $administratorMcpTokenHasher = $this->createAdministratorMcpTokenHasher();
        $administratorMcpToken = $this->createAdministratorMcpToken(
            $publicTokenId,
            $administratorMcpTokenHasher->hash('valid-secret'),
        );
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([$publicTokenId => $administratorMcpToken]),
            $administratorMcpTokenHasher,
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString($publicTokenId . '.invalid-secret');

        $this->assertNull($foundAdministratorMcpToken);
    }

    public function testFindValidTokenByTokenStringReturnsNullForMalformedToken(): void
    {
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([]),
            $this->createAdministratorMcpTokenHasher(),
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString('malformed-token');

        $this->assertNull($foundAdministratorMcpToken);
    }

    public function testFindValidTokenByTokenStringReturnsNullWhenPublicTokenIdDoesNotExist(): void
    {
        $publicTokenId = 'missing-public-token-id';
        $administratorMcpTokenLookup = new AdministratorMcpTokenLookup(
            $this->createAdministratorMcpTokenRepository([]),
            $this->createAdministratorMcpTokenHasher(),
        );

        $foundAdministratorMcpToken = $administratorMcpTokenLookup->findValidTokenByTokenString($publicTokenId . '.secret-token');

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
    ): AdministratorMcpToken {
        $administratorMcpTokenData = new AdministratorMcpTokenData();
        $administratorMcpTokenData->administrator = $this->createStub(Administrator::class);
        $administratorMcpTokenData->publicTokenId = $publicTokenId;
        $administratorMcpTokenData->secretHash = $secretHash;
        $administratorMcpTokenData->createdAt = new DateTimeImmutable('2026-03-18 13:00:00');

        return new AdministratorMcpToken($administratorMcpTokenData);
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

            public function findActiveByPublicTokenId(string $publicTokenId): ?AdministratorMcpToken
            {
                return $this->administratorMcpTokensByPublicTokenId[$publicTokenId] ?? null;
            }
        };
    }
}
