<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingLastAdministratorException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSelfException;
use Shopsys\FrameworkBundle\Model\Administrator\Exception\DeletingSuperadminException;
use Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFacade;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AdministratorFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository $administratorRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFactory $administratorFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Role\AdministratorRoleFacade $administratorRoleFacade
     * @param \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Psr\Clock\ClockInterface $clock
     * @param \Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator $currentAdministrator
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly AdministratorRepository $administratorRepository,
        protected readonly AdministratorFactory $administratorFactory,
        protected readonly AdministratorRoleFacade $administratorRoleFacade,
        protected readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly ClockInterface $clock,
        protected readonly CurrentAdministrator $currentAdministrator,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $administratorData
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function create(AdministratorData $administratorData): Administrator
    {
        $administrator = $this->administratorFactory->create($administratorData);

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
    public function edit($administratorId, AdministratorData $administratorData): Administrator
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $administrator->edit($administratorData);

        $this->em->flush();

        $this->administratorRoleFacade->refreshAdministratorRoles($administrator, $administratorData->roles);

        return $administrator;
    }

    /**
     * @param int $administratorId
     */
    public function delete(int $administratorId): void
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $this->checkForDelete($administrator);
        $this->em->remove($administrator);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    protected function checkForDelete(Administrator $administrator): void
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
     * @param int $administratorId
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getById(int $administratorId): Administrator
    {
        return $this->administratorRepository->getById($administratorId);
    }

    /**
     * @param string $administratorUserName
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getByUserName(string $administratorUserName): Administrator
    {
        return $this->administratorRepository->getByUserName($administratorUserName);
    }

    /**
     * @param string $administratorEmail
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getByEmail(string $administratorEmail): Administrator
    {
        return $this->administratorRepository->getByEmail($administratorEmail);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllListableExcludingSuperadminQueryBuilder(): QueryBuilder
    {
        return $this->administratorRepository->getAllListableExcludingSuperadminQueryBuilder();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->administratorRepository->getAllQueryBuilder();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function setRolesChangedNow(Administrator $administrator): void
    {
        $administrator->setRolesChangedNow();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     */
    public function setAdministratorTransferIssuesLastSeenDateTime(Administrator $administrator): void
    {
        $administrator->setTransferIssuesLastSeenDateTime($this->clock->now());
        $this->em->flush();
    }

    /**
     * @param int $roleGroupId
     * @return string[]
     */
    public function findAdministratorNamesWithRoleGroup(int $roleGroupId): array
    {
        return $this->administratorRepository->findAdministratorNamesWithRoleGroup($roleGroupId);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator|null
     */
    public function findByUuid(string $uuid): ?Administrator
    {
        return $this->administratorRepository->findByUuid($uuid);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function getCurrentlyLoggedAdministrator(): Administrator
    {
        return $this->currentAdministrator->getCurrentlyLoggedAdministrator();
    }
}
