<?php

declare(strict_types=1);

namespace App\Model\Administrator;

use App\Model\Security\Roles;
use Doctrine\ORM\QueryBuilder;
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
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllListableQueryBuilder(): QueryBuilder
    {
        return $this->getAdministratorRepository()->createQueryBuilder('a')
            ->leftJoin('a.roles', 'ar')
            ->where('ar.role = :role')
            ->orWhere('a.roleGroup is not NULL')
            ->setParameter('role', Roles::ROLE_ADMIN);
    }
}
