<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingLastAdministratorException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSelfException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSuperadminException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DuplicateUserNameException;
use Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class AdministratorFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository
     */
    protected $administratorRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFactoryInterface
     */
    protected $administratorFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFacade
     */
    protected $administratorRoleFacade;

    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository $administratorRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFactoryInterface $administratorFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFacade $administratorRoleFacade
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     */
    public function __construct(
        EntityManagerInterface $em,
        AdministratorRepository $administratorRepository,
        AdministratorFactoryInterface $administratorFactory,
        AdministratorRoleFacade $administratorRoleFacade,
        EncoderFactoryInterface $encoderFactory,
        TokenStorageInterface $tokenStorage
    ) {
        $this->administratorRepository = $administratorRepository;
        $this->em = $em;
        $this->administratorFactory = $administratorFactory;
        $this->administratorRoleFacade = $administratorRoleFacade;
        $this->encoderFactory = $encoderFactory;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function create(AdministratorData $administratorData)
    {
        $administratorByUserName = $this->administratorRepository->findByUserName($administratorData->username);
        if ($administratorByUserName !== null) {
            throw new DuplicateUserNameException($administratorByUserName->getUsername());
        }
        $administrator = $this->administratorFactory->create($administratorData);
        $this->setPassword($administrator, $administratorData->password);

        $this->em->persist($administrator);
        $this->em->flush();

        $this->administratorRoleFacade->refreshAdministratorRoles($administrator, $administratorData->roles);

        return $administrator;
    }

    /**
     * @param int $administratorId
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function edit($administratorId, AdministratorData $administratorData)
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $this->checkUsername($administrator, $administratorData->username);
        $administrator->edit($administratorData);
        if ($administratorData->password !== null) {
            $this->setPassword($administrator, $administratorData->password);
        }

        $this->em->flush();

        $this->administratorRoleFacade->refreshAdministratorRoles($administrator, $administratorData->roles);

        return $administrator;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $username
     */
    protected function checkUsername(Administrator $administrator, string $username): void
    {
        $administratorByUserName = $this->administratorRepository->findByUserName($username);
        if ($administratorByUserName !== null
            && $administratorByUserName !== $administrator
            && $administratorByUserName->getUsername() === $username
        ) {
            throw new DuplicateUserNameException($administrator->getUsername());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $password
     */
    protected function setPassword(Administrator $administrator, string $password): void
    {
        $encoder = $this->encoderFactory->getEncoder($administrator);
        $passwordHash = $encoder->encodePassword($password, $administrator->getSalt());
        $administrator->setPasswordHash($passwordHash);
    }

    /**
     * @param int $administratorId
     */
    public function delete($administratorId)
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $this->checkForDelete($administrator);
        $this->em->remove($administrator);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    protected function checkForDelete(Administrator $administrator)
    {
        $adminCountExcludingSuperadmin = $this->administratorRepository->getCountExcludingSuperadmin();
        if ($adminCountExcludingSuperadmin === 1) {
            throw new DeletingLastAdministratorException();
        }
        if ($this->tokenStorage->getToken()->getUser() === $administrator) {
            throw new DeletingSelfException();
        }
        if ($administrator->isSuperadmin()) {
            throw new DeletingSuperadminException();
        }
    }

    /**
     * @param string $administratorUsername
     * @param string $newPassword
     */
    public function changePassword($administratorUsername, $newPassword)
    {
        $administrator = $this->administratorRepository->getByUserName($administratorUsername);
        $this->setPassword($administrator, $newPassword);
        $this->em->flush();
    }

    /**
     * @param int $administratorId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getById($administratorId)
    {
        return $this->administratorRepository->getById($administratorId);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllListableQueryBuilder()
    {
        return $this->administratorRepository->getAllListableQueryBuilder();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function setRolesChangedNow(Administrator $administrator)
    {
        $administrator->setRolesChangedNow();
        $this->em->flush();
    }
}
