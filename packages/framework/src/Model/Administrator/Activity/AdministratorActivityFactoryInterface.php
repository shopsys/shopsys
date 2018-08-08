<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

interface AdministratorActivityFactoryInterface
{
    public function create(Administrator $administrator, string $ipAddress): AdministratorActivity;
}
