<?php

declare(strict_types=1);

namespace App\Model\Administrator\RoleGroup;

use Doctrine\ORM\QueryBuilder;

class AdministratorRoleGroupFacade
{
    /**
     * @var \App\Model\Administrator\RoleGroup\AdministratorRoleGroupRepository
     */
    private AdministratorRoleGroupRepository $administratorRoleGroupRepository;

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupRepository $administratorRoleGroupRepository
     */
    public function __construct(AdministratorRoleGroupRepository $administratorRoleGroupRepository)
    {
        $this->administratorRoleGroupRepository = $administratorRoleGroupRepository;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->administratorRoleGroupRepository->getAllQueryBuilder();
    }
}
