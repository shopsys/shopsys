<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class LoginAsUserExchangeTokenFacade
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeTokenRepository $loginAsUserExchangeTokenRepository
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeTokenFactory $loginAsUserExchangeTokenFactory
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory
     */
    public function __construct(
        protected readonly LoginAsUserExchangeTokenRepository $loginAsUserExchangeTokenRepository,
        protected readonly LoginAsUserExchangeTokenFactory $loginAsUserExchangeTokenFactory,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return string
     */
    public function createAndGetUnencryptedToken(
        CustomerUser $customerUser,
        Administrator $administrator,
    ): string {
        $unencryptedToken = bin2hex(random_bytes(32));
        $expiresAt = new DateTime('+2 minutes');

        $passwordHasher = $this->passwordHasherFactory->getPasswordHasher(Administrator::class);
        $hashedToken = $passwordHasher->hash($unencryptedToken);

        $exchangeToken = $this->loginAsUserExchangeTokenFactory->create($hashedToken, $customerUser, $administrator, $expiresAt);

        $this->entityManager->persist($exchangeToken);
        $this->entityManager->flush();

        return $unencryptedToken;
    }

    /**
     * @param string $exchangeToken
     * @return \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeToken|null
     */
    public function findValidByToken(string $exchangeToken): ?LoginAsUserExchangeToken
    {
        return $this->loginAsUserExchangeTokenRepository->findValidByToken($exchangeToken);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Security\LoginAsUserExchangeToken $exchangeTokenEntity
     */
    public function delete(LoginAsUserExchangeToken $exchangeTokenEntity): void
    {
        $this->entityManager->remove($exchangeTokenEntity);
        $this->entityManager->flush();
    }
}
