<?php

declare(strict_types=1);


namespace App\Model\Administrator;

use App\Model\Security\Roles;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorRepository as BaseAdministratorRepository;

/**
 * @method \App\Model\Administrator\Administrator|null findById(int $administratorId)
 * @method \App\Model\Administrator\Administrator getById(int $administratorId)
 * @method \App\Model\Administrator\Administrator getByValidMultidomainLoginToken(string $multidomainLoginToken)
 * @method \App\Model\Administrator\Administrator|null findByUserName(string $administratorUserName)
 * @method \App\Model\Administrator\Administrator getByUserName(string $administratorUserName)
 */
class AdministratorRepository extends BaseAdministratorRepository
{
    public function getAllListableQueryBuilder()
    {
        return $this->getAdministratorRepository()->createQueryBuilder('a')
            ->join('a.roles', 'ar')
            ->where('ar.role IN (:roles)')
            ->setParameter('roles', array_values(Roles::AVAILABLE_ADMINISTRATOR_ROLES))
            ->groupBy('a')
            ;
    }
}
