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

    /**
     * @param int $roleGroupId
     * @return string[]
     */
    public function findAdministratorNamesWithRoleGroup(int $roleGroupId): array
    {
        $administrators = $this->getAdministratorRepository()
            ->createQueryBuilder('a')
            ->select('a.realName')
            ->where('a.roleGroup = :roleGroupId')
            ->setParameter('roleGroupId', $roleGroupId)
            ->getQuery()
            ->getArrayResult();

        return array_map(function ($item) {
            return $item['realName'];
        }, $administrators);
    }

    /**
     * @param string $uuid
     * @return \App\Model\Administrator\Administrator|null
     */
    public function findByUuid(string $uuid): ?Administrator
    {
        return $this->getAdministratorRepository()->findOneBy(['uuid' => $uuid]);
    }
}
