<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

interface AdministratorDataFactoryInterface
{
    public function create(): AdministratorData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function createFromAdministrator(Administrator $administrator): AdministratorData;
}
