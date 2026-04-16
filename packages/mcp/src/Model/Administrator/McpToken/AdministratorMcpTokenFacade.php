<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorMcpTokenFacade
{
    public function __construct(
        protected readonly AdministratorMcpTokenDataFactory $administratorMcpTokenDataFactory,
        protected readonly AdministratorMcpTokenFactory $administratorMcpTokenFactory,
        protected readonly AdministratorMcpTokenGenerator $administratorMcpTokenGenerator,
        protected readonly AdministratorMcpTokenHasher $administratorMcpTokenHasher,
        protected readonly AdministratorMcpTokenLookup $administratorMcpTokenLookup,
        protected readonly AdministratorMcpTokenRepository $administratorMcpTokenRepository,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ClockInterface $clock,
        protected readonly int $accessTokenTtlSeconds,
    ) {
    }

    public function issueManualTokenForAdministrator(Administrator $administrator): AdministratorMcpIssuedToken
    {
        return $this->issueTokenForAdministratorAndClient(
            $administrator,
            AdministratorMcpToken::MANUAL_CLIENT_ID,
            AdministratorMcpToken::MANUAL_CLIENT_NAME,
        );
    }

    public function issueTokenForAdministratorAndClient(
        Administrator $administrator,
        string $clientId,
        string $clientName,
    ): AdministratorMcpIssuedToken {
        $now = $this->clock->now();
        $existingAdministratorMcpToken = $this->administratorMcpTokenRepository->findCurrentByAdministratorAndClient($administrator, $clientId);

        if ($existingAdministratorMcpToken !== null) {
            $existingAdministratorMcpToken->replace($now);
            $this->entityManager->flush();
        }

        $expiresAt = $now->modify(sprintf('+%d seconds', $this->accessTokenTtlSeconds));
        $issuedToken = $this->administratorMcpTokenGenerator->generateIssuedToken($expiresAt);

        $administratorMcpTokenData = $this->administratorMcpTokenDataFactory->create();
        $administratorMcpTokenData->administrator = $administrator;
        $administratorMcpTokenData->publicTokenId = $issuedToken->publicTokenId;
        $administratorMcpTokenData->secretHash = $this->administratorMcpTokenHasher->hash($issuedToken->secret);
        $administratorMcpTokenData->clientId = $clientId;
        $administratorMcpTokenData->clientName = $clientName;
        $administratorMcpTokenData->createdAt = $now;
        $administratorMcpTokenData->expiresAt = $issuedToken->expiresAt;

        $administratorMcpToken = $this->administratorMcpTokenFactory->create($administratorMcpTokenData);

        $this->entityManager->persist($administratorMcpToken);
        $this->entityManager->flush();

        return $issuedToken;
    }

    public function getRemainingLifetimeInSeconds(AdministratorMcpIssuedToken $issuedToken): int
    {
        return max($issuedToken->expiresAt->getTimestamp() - $this->clock->now()->getTimestamp(), 0);
    }

    public function findActiveManualTokenByAdministrator(Administrator $administrator): ?AdministratorMcpToken
    {
        return $this->findActiveByAdministratorAndClient($administrator, AdministratorMcpToken::MANUAL_CLIENT_ID);
    }

    public function findActiveByAdministratorAndClient(
        Administrator $administrator,
        string $clientId,
    ): ?AdministratorMcpToken {
        $administratorMcpToken = $this->administratorMcpTokenRepository->findCurrentByAdministratorAndClient($administrator, $clientId);

        if ($administratorMcpToken === null || !$administratorMcpToken->isValidAt($this->clock->now())) {
            return null;
        }

        return $administratorMcpToken;
    }

    public function findValidTokenByTokenString(string $tokenString): ?AdministratorMcpToken
    {
        return $this->administratorMcpTokenLookup->findValidTokenByTokenString($tokenString);
    }

    /**
     * @return array<\Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken>
     */
    public function findActiveConnectedClientTokensByAdministrator(Administrator $administrator): array
    {
        return $this->administratorMcpTokenRepository->findActiveConnectedClientTokensByAdministrator(
            $administrator,
            $this->clock->now(),
        );
    }

    public function revokeManualTokenForAdministrator(Administrator $administrator): void
    {
        $this->revokeTokenForAdministratorAndClient($administrator, AdministratorMcpToken::MANUAL_CLIENT_ID);
    }

    public function revokeTokenForAdministratorAndClient(Administrator $administrator, string $clientId): void
    {
        $administratorMcpToken = $this->administratorMcpTokenRepository->findCurrentByAdministratorAndClient($administrator, $clientId);

        if ($administratorMcpToken === null) {
            return;
        }

        $administratorMcpToken->revoke($this->clock->now());
        $this->entityManager->flush();
    }

    public function markTokenUsed(AdministratorMcpToken $administratorMcpToken): void
    {
        $administratorMcpToken->markUsed($this->clock->now());
        $this->entityManager->flush();
    }
}
