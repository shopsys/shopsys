<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Overblog\GraphQLBundle\Definition\Argument;

interface RegistrationDataFactoryInterface
{
    /**
     * @param int $domainId
     * @return \App\Model\Customer\User\RegistrationData
     */
    public function createForDomainId(int $domainId): RegistrationData;

    /**
     * @return \App\Model\Customer\User\RegistrationData
     */
    public function create(): RegistrationData;

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \App\Model\Customer\User\RegistrationData
     */
    public function createWithArgument(Argument $argument): RegistrationData;
}
