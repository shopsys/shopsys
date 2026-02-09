<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class LoginAsUserExchangeTokenFacade
{
    public function __construct(
        protected readonly LoginAsUserExchangeTokenRepository $loginAsUserExchangeTokenRepository,
        protected readonly LoginAsUserExchangeTokenFactory $loginAsUserExchangeTokenFactory,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function createAndGetUnencryptedToken(
        CustomerUser $customerUser,
        Administrator $administrator,
    ): string {
        $unencryptedToken = bin2hex(random_bytes(32));
        $expiresAt = $this->clock->now()->modify('+2 minutes');

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(Administrator::class);
        $hashedToken = $passwordHasher->hash($unencryptedToken);

        $exchangeToken = $this->loginAsUserExchangeTokenFactory->create($hashedToken, $customerUser, $administrator, $expiresAt);

        $this->entityManager->persist($exchangeToken);
        $this->entityManager->flush();

        return $unencryptedToken;
    }

    public function findValidByToken(string $exchangeToken): ?LoginAsUserExchangeToken
    {
        return $this->loginAsUserExchangeTokenRepository->findValidByToken($exchangeToken);
    }

    public function delete(LoginAsUserExchangeToken $exchangeTokenEntity): void
    {
        $this->entityManager->remove($exchangeTokenEntity);
        $this->entityManager->flush();
    }

    public function deleteAllExpired(): void
    {
        $this->loginAsUserExchangeTokenRepository->deleteAllExpired();
    }
}
