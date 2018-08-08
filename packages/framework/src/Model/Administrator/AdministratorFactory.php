<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

class AdministratorFactory implements AdministratorFactoryInterface
{
    public function create(AdministratorData $data): Administrator
    {
        return new Administrator($data);
    }
}
