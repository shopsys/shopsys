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

    public function create(AdministratorData $administratorData): Administrator
    {
        $administrator = $this->administratorFactory->create($administratorData);

        $this->em->persist($administrator);
        $this->em->flush();

        $this->administratorRoleFacade->refreshAdministratorRoles($administrator, $administratorData->roles);

        return $administrator;
    }

    public function edit(int $administratorId, AdministratorData $administratorData): Administrator
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $administrator->edit($administratorData);

        $this->em->flush();

        $this->administratorRoleFacade->refreshAdministratorRoles($administrator, $administratorData->roles);

        return $administrator;
    }

    public function delete(int $administratorId): void
    {
        $administrator = $this->administratorRepository->getById($administratorId);
        $this->checkForDelete($administrator);
        $this->em->remove($administrator);
        $this->em->flush();
    }

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

    public function getById(int $administratorId): Administrator
    {
        return $this->administratorRepository->getById($administratorId);
    }

    public function getByUserName(string $administratorUserName): Administrator
    {
        return $this->administratorRepository->getByUserName($administratorUserName);
    }

    public function getByEmail(string $administratorEmail): Administrator
    {
        return $this->administratorRepository->getByEmail($administratorEmail);
    }

    public function getAllListableExcludingSuperadminQueryBuilder(): QueryBuilder
    {
        return $this->administratorRepository->getAllListableExcludingSuperadminQueryBuilder();
    }

    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->administratorRepository->getAllQueryBuilder();
    }

    public function setRolesChangedNow(Administrator $administrator): void
    {
        $administrator->setRolesChangedNow();
        $this->em->flush();
    }

    public function setAdministratorTransferIssuesLastSeenDateTime(Administrator $administrator): void
    {
        $administrator->setTransferIssuesLastSeenDateTime($this->clock->now());
        $this->em->flush();
    }

    /**
     * @return string[]
     */
    public function findAdministratorNamesWithRoleGroup(int $roleGroupId): array
    {
        return $this->administratorRepository->findAdministratorNamesWithRoleGroup($roleGroupId);
    }

    public function findByUuid(string $uuid): ?Administrator
    {
        return $this->administratorRepository->findByUuid($uuid);
    }

    public function getCurrentlyLoggedAdministrator(): Administrator
    {
        return $this->currentAdministrator->getCurrentlyLoggedAdministrator();
    }
}
