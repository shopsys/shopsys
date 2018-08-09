<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

interface AdministratorFactoryInterface
{
    public function create(AdministratorData $data): Administrator;
}
