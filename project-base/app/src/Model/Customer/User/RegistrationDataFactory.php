<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\RegistrationDataFactory as BaseRegistrationDataFactory;

/**
 * @method \App\Model\Customer\User\RegistrationData createForDomainId(int $domainId)
 * @method \App\Model\Customer\User\RegistrationData createFromSocialNetworkProfile(\Hybridauth\User\Profile $profile)
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
