<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\RegistrationDataFactory as BaseRegistrationDataFactory;

/**
 * @method \App\Model\Customer\User\RegistrationData createForDomainId(int $domainId)
 */
class RegistrationDataFactory extends BaseRegistrationDataFactory
{
    /**
     * @return \App\Model\Customer\User\RegistrationData
     */
    public function create(): RegistrationData
    {
        return new RegistrationData();
    }
}
