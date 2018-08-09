<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface UserDataFactoryInterface
{
    public function create(): UserData;

    public function createForDomainId(int $domainId): UserData;

    public function createFromUser(User $user): UserData;
}
