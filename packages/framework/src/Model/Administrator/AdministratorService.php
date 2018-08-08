<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdministratorService
{
    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        TokenStorageInterface $tokenStorage
    ) {
        $this->encoderFactory = $encoderFactory;
        $this->tokenStorage = $tokenStorage;
    }
    
    public function setPassword(Administrator $administrator, string $password): void
    {
        $encoder = $this->encoderFactory->getEncoder($administrator);
        $passwordHash = $encoder->encodePassword($password, $administrator->getSalt());
        $administrator->setPassword($passwordHash);
    }
    
    public function delete(Administrator $administrator, int $adminCountExcludingSuperadmin): void
    {
        if ($adminCountExcludingSuperadmin === 1) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingLastAdministratorException();
        }
        if ($this->tokenStorage->getToken()->getUser() === $administrator) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSelfException();
        }
        if ($administrator->isSuperadmin()) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSuperadminException();
        }
    }

    public function edit(
        AdministratorData $administratorData,
        Administrator $administrator,
        Administrator $administratorByUserName = null
    ): \Shopsys\FrameworkBundle\Model\Administrator\Administrator {
        if ($administratorByUserName !== null
            && $administratorByUserName !== $administrator
            && $administratorByUserName->getUsername() === $administratorData->username
        ) {
            throw new \Shopsys\FrameworkBundle\Model\Administrator\Exception\DuplicateUserNameException($administrator->getUsername());
        }
        $administrator->edit($administratorData);
        if ($administratorData->password !== null) {
            $this->setPassword($administrator, $administratorData->password);
        }

        return $administrator;
    }
}
