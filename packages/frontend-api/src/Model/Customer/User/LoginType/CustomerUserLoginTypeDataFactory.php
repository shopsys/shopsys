<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CustomerUserLoginTypeDataFactory
{
    /**
     * @param \Psr\Clock\ClockInterface $clock
     */
    public function __construct(
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $loginType
     * @param string|null $externalId
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeData
     */
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

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeData
     */
    protected function createInstance(): CustomerUserLoginTypeData
    {
        return new CustomerUserLoginTypeData();
    }
}
