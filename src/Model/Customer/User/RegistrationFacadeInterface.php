<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

interface RegistrationFacadeInterface
{
    /**
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function register(RegistrationData $registrationData): CustomerUser;

    /**
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return \App\Model\Customer\User\CustomerUser
     */
    public function registerCompany(RegistrationData $registrationData): CustomerUser;
}
