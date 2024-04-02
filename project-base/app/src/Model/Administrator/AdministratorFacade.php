<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade as BaseAdministratorFacade;

/**
 * @method \App\Model\Administrator\Administrator create(\App\Model\Administrator\AdministratorData $administratorData)
 * @method \App\Model\Administrator\Administrator edit(int $administratorId, \App\Model\Administrator\AdministratorData $administratorData)
 * @method checkUsername(\App\Model\Administrator\Administrator $administrator, string $username)
 * @method setPassword(\App\Model\Administrator\Administrator $administrator, string $password)
 * @method checkForDelete(\App\Model\Administrator\Administrator $administrator)
 * @method \App\Model\Administrator\Administrator getById(int $administratorId)
 * @method setRolesChangedNow(\App\Model\Administrator\Administrator $administrator)
 * @property \App\Model\Administrator\AdministratorRepository $administratorRepository
 * @property \App\Model\Administrator\Role\AdministratorRoleFacade $administratorRoleFacade
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Administrator\AdministratorRepository $administratorRepository, \Shopsys\FrameworkBundle\Model\Administrator\AdministratorFactoryInterface $administratorFactory, \App\Model\Administrator\Role\AdministratorRoleFacade $administratorRoleFacade, \Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface $passwordHasherFactory, \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage)
 * @method setAdministratorTransferIssuesLastSeenDateTime(\App\Model\Administrator\Administrator $administrator)
 */
class AdministratorFacade extends BaseAdministratorFacade
{
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
     * @return \App\Model\Administrator\Administrator|null
     */
    public function findByUuid(string $uuid): ?Administrator
    {
        return $this->administratorRepository->findByUuid($uuid);
    }
}
