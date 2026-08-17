<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class LoginAsUserExchangeTokenRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function findValidByToken(string $unencryptedToken): ?LoginAsUserExchangeToken
    {
        $validTokens = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(LoginAsUserExchangeToken::class, 'e')
            ->where('e.expiresAt > :now')
            ->setParameter('now', $this->clock->now())
            ->getQuery()
            ->getResult();

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(Administrator::class);

        foreach ($validTokens as $tokenEntity) {
            if ($passwordHasher->verify($tokenEntity->getToken(), $unencryptedToken)) {
                return $tokenEntity;
            }
        }

        return null;
    }

    public function deleteAllExpired(): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(LoginAsUserExchangeToken::class, 'e')
            ->where('e.expiresAt < :now')
            ->setParameter('now', $this->clock->now())
            ->getQuery()
            ->execute();
    }
}
