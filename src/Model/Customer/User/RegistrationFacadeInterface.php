<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

interface RegistrationFacadeInterface
{
    /**
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return mixed
     */
    public function register(RegistrationData $registrationData): CustomerUser;
}
