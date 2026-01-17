<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CustomerUserLoginTypeDataFactory
{
    public function __construct(
        protected readonly ClockInterface $clock,
    ) {
    }

    public function create(
        CustomerUser $customerUser,
        string $loginType,
        ?string $externalId = null,
    ): CustomerUserLoginTypeData {
        $customerUserLoginTypeData = $this->createInstance();

        $customerUserLoginTypeData->customerUser = $customerUser;
        $customerUserLoginTypeData->loginType = $loginType;
        $customerUserLoginTypeData->externalId = $externalId;
        $customerUserLoginTypeData->lastLoggedInAt = $this->clock->now();

        return $customerUserLoginTypeData;
    }

    protected function createInstance(): CustomerUserLoginTypeData
    {
        return new CustomerUserLoginTypeData();
    }
}
