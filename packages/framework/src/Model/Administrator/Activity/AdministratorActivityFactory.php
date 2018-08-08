<?php

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityFactory implements AdministratorActivityFactoryInterface
{

    public function create(Administrator $administrator, string $ipAddress): AdministratorActivity
    {
        return new AdministratorActivity($administrator, $ipAddress);
    }
}
