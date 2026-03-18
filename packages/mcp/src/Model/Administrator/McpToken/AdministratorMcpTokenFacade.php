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
    ) {
    }

    public function generateTokenForAdministrator(Administrator $administrator): string
    {
        $now = $this->clock->now();
        $existingAdministratorMcpToken = $this->administratorMcpTokenRepository->findActiveByAdministrator($administrator);

        if ($existingAdministratorMcpToken !== null) {
            $existingAdministratorMcpToken->replace($now);
            $this->entityManager->flush();
        }

        $generatedTokenPair = $this->administratorMcpTokenGenerator->generateTokenPair();
        $administratorMcpTokenData = $this->administratorMcpTokenDataFactory->create();
        $administratorMcpTokenData->administrator = $administrator;
        $administratorMcpTokenData->publicTokenId = $generatedTokenPair->publicTokenId;
        $administratorMcpTokenData->secretHash = $this->administratorMcpTokenHasher->hash($generatedTokenPair->secret);
        $administratorMcpTokenData->createdAt = $now;

        $administratorMcpToken = $this->administratorMcpTokenFactory->create($administratorMcpTokenData);

        $this->entityManager->persist($administratorMcpToken);
        $this->entityManager->flush();

        return $generatedTokenPair->getTokenString();
    }

    public function findActiveByAdministrator(Administrator $administrator): ?AdministratorMcpToken
    {
        return $this->administratorMcpTokenRepository->findActiveByAdministrator($administrator);
    }

    public function findValidTokenByTokenString(string $tokenString): ?AdministratorMcpToken
    {
        return $this->administratorMcpTokenLookup->findValidTokenByTokenString($tokenString);
    }

    public function revokeTokenForAdministrator(Administrator $administrator): void
    {
        $administratorMcpToken = $this->administratorMcpTokenRepository->findActiveByAdministrator($administrator);

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
