<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

class RegistrationDataFactory implements RegistrationDataFactoryInterface
{
    /**
     * @param int $domainId
     * @return \App\Model\Customer\User\RegistrationData
     */
    public function createForDomainId(int $domainId): RegistrationData
    {
        $registrationData = $this->create();
        $registrationData->domainId = $domainId;
        return $registrationData;
    }

    /**
     * @return \App\Model\Customer\User\RegistrationData
     */
    public function create(): RegistrationData
    {
        return new RegistrationData();
    }
}
