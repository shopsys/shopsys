<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

class AdministratorDataFactory implements AdministratorDataFactoryInterface
{
    public function create(): AdministratorData
    {
        return new AdministratorData();
    }

    public function createFromAdministrator(Administrator $administrator): AdministratorData
    {
        $administratorData = new AdministratorData();
        $this->fillFromAdministrator($administratorData, $administrator);
        return $administratorData;
    }

    protected function fillFromAdministrator(AdministratorData $administratorData, Administrator $administrator)
    {
        $administratorData->email = $administrator->getEmail();
        $administratorData->realName = $administrator->getRealName();
        $administratorData->username = $administrator->getUsername();
        $administratorData->superadmin = $administrator->isSuperadmin();
    }
}
