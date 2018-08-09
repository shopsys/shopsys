<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

interface AdministratorGridLimitFactoryInterface
{
    public function create(Administrator $administrator, string $gridId, int $limit): AdministratorGridLimit;
}
