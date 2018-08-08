<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

class AdministratorGridLimitFactory implements AdministratorGridLimitFactoryInterface
{
    public function create(Administrator $administrator, string $gridId, int $limit): AdministratorGridLimit
    {
        return new AdministratorGridLimit($administrator, $gridId, $limit);
    }
}
