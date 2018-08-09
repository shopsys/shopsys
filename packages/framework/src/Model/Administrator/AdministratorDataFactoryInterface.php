<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

interface AdministratorDataFactoryInterface
{
    public function create(): AdministratorData;

    public function createFromAdministrator(Administrator $administrator): AdministratorData;
}
