<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface CustomerDataFactoryInterface
{
    public function create(): CustomerData;

    public function createFromUser(User $user): CustomerData;
}
