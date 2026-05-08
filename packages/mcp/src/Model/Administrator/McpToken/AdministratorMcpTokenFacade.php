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
        return $this->issueToken(
            $administrator,
            AdministratorMcpToken::TYPE_MANUAL,
            null,
            AdministratorMcpToken::DEFAULT_MANUAL_TOKEN_LABEL,
        );
    }

    public function issueTokenForAdministratorAndClient(
        Administrator $administrator,
        string $clientId,
        string $label,
    ): AdministratorMcpIssuedToken {
        return $this->issueToken(
            $administrator,
            AdministratorMcpToken::TYPE_OAUTH,
            $clientId,
            $label,
        );
    }

    public function findValidTokenByTokenString(string $tokenString): ?AdministratorMcpToken
    {
        return $this->administratorMcpTokenLookup->findValidTokenByTokenString($tokenString);
    }

    public function findActiveByIdAndAdministrator(Administrator $administrator, int $id): ?AdministratorMcpToken
    {
        return $this->administratorMcpTokenRepository->findActiveByIdAndAdministrator(
            $administrator,
            $id,
            $this->clock->now(),
        );
    }

    public function revokeToken(AdministratorMcpToken $administratorMcpToken): void
    {
        $administratorMcpToken->revoke($this->clock->now());
        $this->entityManager->flush();
    }

    public function markTokenUsed(AdministratorMcpToken $administratorMcpToken): void
    {
        $administratorMcpToken->markUsed($this->clock->now());
        $this->entityManager->flush();
    }

    protected function issueToken(
        Administrator $administrator,
        string $type,
        ?string $clientId,
        string $label,
    ): AdministratorMcpIssuedToken {
        $now = $this->clock->now();
        $expiresAt = $now->modify(sprintf('+%d seconds', $this->accessTokenTtlSeconds));
        $issuedToken = $this->administratorMcpTokenGenerator->generateIssuedToken($expiresAt);

        $administratorMcpTokenData = $this->administratorMcpTokenDataFactory->create();
        $administratorMcpTokenData->administrator = $administrator;
        $administratorMcpTokenData->publicTokenId = $issuedToken->publicTokenId;
        $administratorMcpTokenData->secretHash = $this->administratorMcpTokenHasher->hash($issuedToken->secret);
        $administratorMcpTokenData->type = $type;
        $administratorMcpTokenData->clientId = $clientId;
        $administratorMcpTokenData->label = $label;
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
}
