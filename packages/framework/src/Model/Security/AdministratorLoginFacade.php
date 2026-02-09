<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdministratorLoginFacade
{
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function invalidateCurrentAdministratorLoginToken(): void
    {
        $token = $this->tokenStorage->getToken();

        if ($token === null) {
            return;
        }

        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $currentAdministrator */
        $currentAdministrator = $token->getUser();
        $currentAdministrator->setLoginToken('');

        $this->em->flush();
    }
}
