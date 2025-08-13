<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class LoginAsUserExchangeTokenRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

    /**
     * @param string $unencryptedToken
     * @return \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeToken|null
     */
    public function findValidByToken(string $unencryptedToken): ?LoginAsUserExchangeToken
    {
        $validTokens = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(LoginAsUserExchangeToken::class, 'e')
            ->where('e.expiresAt > :now')
            ->setParameter('now', new DateTime())
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

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeToken $exchangeToken
     */
    public function remove(LoginAsUserExchangeToken $exchangeToken): void
    {
        $this->entityManager->remove($exchangeToken);
        $this->entityManager->flush();
    }

    public function deleteAllExpired(): void
    {
        $this->entityManager->createQueryBuilder()
            ->delete(LoginAsUserExchangeToken::class, 'e')
            ->where('e.expiresAt < :now')
            ->setParameter('now', new DateTime())
            ->getQuery()
            ->execute();
    }
}
